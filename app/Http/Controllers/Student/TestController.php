<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * Показать список экзаменов для студента
     */
    public function index()
    {
        $student = Auth::user();
        
        // Получаем все расписания, где студент является участником группы
        $schedules = Schedule::whereHas('group', function ($query) use ($student) {
            $query->whereHas('students', function ($q) use ($student) {
                $q->where('users.id', $student->id);
            });
        })
        ->with(['subject', 'group', 'teacher'])
        ->get();

        // Получаем все тесты для этих расписаний
        $tests = Test::whereIn('schedule_id', $schedules->pluck('id'))
            ->where('is_active', true)
            ->whereIn('exam_type', ['periodic', 'final'])
            ->with(['schedule.subject', 'schedule.group'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Группируем по расписаниям
        $examsBySchedule = $tests->groupBy('schedule_id')->map(function ($scheduleTests, $scheduleId) use ($student) {
            $schedule = $scheduleTests->first()->schedule;
            
            if (!$schedule) {
                return null;
            }
            
            // Разделяем на периодические и итоговые
            $periodicExams = $scheduleTests->where('exam_type', 'periodic')->sortBy('id');
            $finalExam = $scheduleTests->where('exam_type', 'final')->first();

            return [
                'schedule' => [
                    'id' => $schedule->id,
                    'subject_name' => $schedule->subject ? $schedule->subject->name : 'Не указан',
                    'group_name' => $schedule->group ? $schedule->group->name : 'Не указана',
                ],
                'periodic_exams' => $periodicExams->map(function ($test) use ($student) {
                    return $this->formatTestForStudent($test, $student);
                })->values(),
                'final_exam' => $finalExam ? $this->formatTestForStudent($finalExam, $student) : null,
            ];
        })->filter()->values();

        return Inertia::render('Student/Tests/Index', [
            'examsBySchedule' => $examsBySchedule,
        ]);
    }

    /**
     * Показать экзамен для прохождения
     */
    public function show(Test $test)
    {
        $student = Auth::user();
        
        // Загружаем связи
        $test->load(['schedule.group.students']);
        
        // Проверяем доступ
        if (!$test->schedule || !$test->schedule->group->students->contains($student->id)) {
            abort(403, 'У вас нет доступа к этому экзамену');
        }

        // Проверяем, активен ли тест
        if (!$test->is_active) {
            return redirect()->route('student.tests.index')
                ->with('error', 'Экзамен неактивен');
        }

        // Получаем количество попыток студента
        $attemptsCount = TestAttempt::where('test_id', $test->id)
            ->where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->count();

        // Проверяем максимальное количество попыток
        $maxAttempts = $test->max_attempts ?? 3;
        if ($attemptsCount >= $maxAttempts) {
            return redirect()->route('student.tests.index')
                ->with('error', 'Вы использовали все попытки для этого экзамена');
        }

        // Загружаем вопросы с ответами
        $test->load(['questions' => function ($query) {
            $query->orderBy('order')->orderBy('id');
        }, 'questions.answers' => function ($query) {
            $query->orderBy('order');
        }]);

        // Получаем предыдущие попытки
        $previousAttempts = TestAttempt::where('test_id', $test->id)
            ->where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'score' => $attempt->score,
                    'correct_answers' => $attempt->correct_answers,
                    'total_questions' => $attempt->total_questions,
                    'is_passed' => $attempt->is_passed,
                    'completed_at' => $attempt->completed_at->format('d.m.Y H:i'),
                ];
            });

        return Inertia::render('Student/Tests/Show', [
            'test' => [
                'id' => $test->id,
                'title' => $test->title,
                'description' => $test->description,
                'time_limit' => $test->time_limit,
                'passing_score' => $test->passing_score,
                'max_attempts' => $maxAttempts,
                'attempts_count' => $attemptsCount,
                'remaining_attempts' => $maxAttempts - $attemptsCount,
                'exam_type' => $test->exam_type,
                'schedule' => [
                    'id' => $test->schedule->id,
                    'subject_name' => $test->schedule->subject ? $test->schedule->subject->name : 'Не указан',
                    'group_name' => $test->schedule->group ? $test->schedule->group->name : 'Не указана',
                ],
                'questions' => $test->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'question' => $question->question,
                        'type' => $question->type,
                        'order' => $question->order,
                        'explanation' => $question->explanation,
                        'answers' => $question->answers->map(function ($answer) {
                            return [
                                'id' => $answer->id,
                                'answer' => $answer->answer,
                                'is_correct' => $answer->is_correct,
                                'order' => $answer->order,
                                'matching_key' => $answer->matching_key,
                                'matching_value' => $answer->matching_value,
                            ];
                        }),
                    ];
                }),
            ],
            'previousAttempts' => $previousAttempts,
        ]);
    }

    /**
     * Начать попытку экзамена
     */
    public function startAttempt(Test $test)
    {
        $student = Auth::user();
        
        // Проверяем доступ
        if (!$test->schedule || !$test->schedule->group->students->contains($student->id)) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет доступа к этому экзамену'
            ], 403);
        }

        // Проверяем количество попыток
        $attemptsCount = TestAttempt::where('test_id', $test->id)
            ->where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->count();

        $maxAttempts = $test->max_attempts ?? 3;
        if ($attemptsCount >= $maxAttempts) {
            return response()->json([
                'success' => false,
                'message' => 'Вы использовали все попытки'
            ], 400);
        }

        // Создаем новую попытку
        $attempt = TestAttempt::create([
            'test_id' => $test->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'is_exam' => in_array($test->exam_type, ['periodic', 'final']),
        ]);

        return response()->json([
            'success' => true,
            'attempt' => [
                'id' => $attempt->id,
                'started_at' => $attempt->started_at->toISOString(),
            ],
        ]);
    }

    /**
     * Завершить попытку экзамена
     */
    public function submitAttempt(Request $request, Test $test)
    {
        $student = Auth::user();
        
        $request->validate([
            'attempt_id' => 'required|exists:test_attempts,id',
            'answers' => 'required|array',
        ]);

        // Проверяем попытку
        $attempt = TestAttempt::where('id', $request->attempt_id)
            ->where('test_id', $test->id)
            ->where('student_id', $student->id)
            ->whereNull('completed_at')
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Попытка не найдена или уже завершена'
            ], 404);
        }

        // Загружаем вопросы с правильными ответами
        $test->load(['questions.answers']);
        
        $totalQuestions = $test->questions->count();
        $correctAnswers = 0;
        $answers = [];

        // Проверяем ответы
        foreach ($test->questions as $question) {
            $userAnswer = $request->answers[$question->id] ?? null;
            
            if ($question->type === 'single_choice') {
                $correctAnswer = $question->answers->where('is_correct', true)->first();
                $isCorrect = $correctAnswer && $userAnswer == $correctAnswer->id;
            } elseif ($question->type === 'multiple_choice') {
                $correctAnswerIds = $question->answers->where('is_correct', true)->pluck('id')->toArray();
                $userAnswerIds = is_array($userAnswer) ? $userAnswer : [];
                sort($correctAnswerIds);
                sort($userAnswerIds);
                $isCorrect = $correctAnswerIds === $userAnswerIds;
            } else {
                $isCorrect = false; // Для других типов вопросов
            }

            if ($isCorrect) {
                $correctAnswers++;
            }

            $answers[$question->id] = $userAnswer;
        }

        // Вычисляем оценку в процентах
        $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
        $isPassed = $score >= $test->passing_score;

        // Вычисляем время прохождения
        $timeSpent = null;
        if ($attempt->started_at) {
            $timeSpent = now()->diffInSeconds($attempt->started_at);
        }

        // Обновляем попытку
        $attempt->update([
            'score' => $score,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'is_passed' => $isPassed,
            'completed_at' => now(),
            'time_spent' => $timeSpent,
            'answers' => $answers,
        ]);

        return response()->json([
            'success' => true,
            'attempt' => [
                'id' => $attempt->id,
                'score' => $score,
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'is_passed' => $isPassed,
                'completed_at' => $attempt->completed_at->toISOString(),
            ],
        ]);
    }

    /**
     * Форматировать тест для студента
     */
    private function formatTestForStudent(Test $test, User $student)
    {
        $attempts = TestAttempt::where('test_id', $test->id)
            ->where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->get();

        $bestAttempt = $attempts->sortByDesc('score')->first();

        return [
            'id' => $test->id,
            'title' => $test->title,
            'description' => $test->description,
            'exam_type' => $test->exam_type,
            'max_attempts' => $test->max_attempts ?? 3,
            'attempts_count' => $attempts->count(),
            'remaining_attempts' => max(0, ($test->max_attempts ?? 3) - $attempts->count()),
            'best_score' => $bestAttempt ? $bestAttempt->score : null,
            'is_passed' => $bestAttempt ? $bestAttempt->is_passed : false,
            'last_attempt_at' => $attempts->first() ? $attempts->first()->completed_at->format('d.m.Y H:i') : null,
        ];
    }
}
