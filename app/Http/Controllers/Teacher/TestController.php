<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * Показать список тестов (расписаний с тестами)
     */
    public function index()
    {
        $teacher = Auth::user();
        
        // Получаем расписания учителя с тестами
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['subject', 'group', 'test.questions'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($schedule) {
                $test = $schedule->test;
                
                return [
                    'id' => $schedule->id,
                    'subject_name' => $schedule->subject ? $schedule->subject->name : 'Не указан',
                    'group_name' => $schedule->group ? $schedule->group->name : 'Не указана',
                    'semester' => $schedule->semester,
                    'study_year' => $schedule->study_year,
                    'has_test' => $test !== null,
                    'test' => $test ? [
                        'id' => $test->id,
                        'title' => $test->title,
                        'description' => $test->description,
                        'time_limit' => $test->time_limit,
                        'passing_score' => $test->passing_score,
                        'max_attempts' => $test->max_attempts,
                        'is_active' => $test->is_active,
                        'questions_count' => $test->questions->count(),
                    ] : null,
                ];
            });

        return Inertia::render('Teacher/Tests/Index', [
            'schedules' => $schedules,
            'stats' => [
                'total_schedules' => $schedules->count(),
                'with_tests' => $schedules->where('has_test', true)->count(),
                'without_tests' => $schedules->where('has_test', false)->count(),
            ]
        ]);
    }

    /**
     * Показать тест для расписания (или создать если его нет)
     */
    public function show(Schedule $schedule)
    {
        // Проверяем доступ
        if ($schedule->teacher_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        // Загружаем необходимые связи
        $schedule->load(['subject', 'group', 'test']);

        // Получаем или создаем тест
        $test = $schedule->test;
        
        if (!$test) {
            // Автоматически создаем тест для расписания
            $test = Test::create([
                'title' => 'Тест: ' . ($schedule->subject ? $schedule->subject->name : 'Расписание'),
                'description' => 'Тест для расписания ' . ($schedule->subject ? $schedule->subject->name : ''),
                'schedule_id' => $schedule->id,
                'created_by' => Auth::id(),
                'time_limit' => null,
                'passing_score' => 60,
                'max_attempts' => 3,
                'is_active' => false,
            ]);
        }

        // Загружаем вопросы с ответами, отсортированные по дате создания в убывающем порядке (новые сверху)
        $test->load(['questions' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'questions.answers']);

        $testData = [
            'id' => $test->id,
            'title' => $test->title,
            'description' => $test->description,
            'time_limit' => $test->time_limit,
            'passing_score' => $test->passing_score,
            'max_attempts' => $test->max_attempts,
            'is_active' => $test->is_active,
            'schedule_id' => $schedule->id,
            'schedule' => [
                'id' => $schedule->id,
                'subject_name' => $schedule->subject ? $schedule->subject->name : 'Не указан',
                'group_name' => $schedule->group ? $schedule->group->name : 'Не указана',
                'semester' => $schedule->semester,
                'study_year' => $schedule->study_year,
            ],
            'questions' => $test->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'type' => $question->type,
                    'order' => $question->order,
                    'explanation' => $question->explanation,
                    'created_at' => $question->created_at ? $question->created_at->toISOString() : null,
                    'answers' => $question->answers->map(function ($answer) {
                        return [
                            'id' => $answer->id,
                            'answer' => $answer->answer,
                            'is_correct' => $answer->is_correct,
                            'order' => $answer->order,
                            'matching_key' => $answer->matching_key,
                            'matching_value' => $answer->matching_value,
                        ];
                    })
                ];
            })
        ];

        return Inertia::render('Teacher/Tests/Show', [
            'test' => $testData
        ]);
    }

    /**
     * Обновить настройки теста
     */
    public function update(Request $request, Test $test)
    {
        // Проверяем доступ
        $this->authorize('update', $test);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $test->update([
            'title' => $request->title,
            'description' => $request->description,
            'time_limit' => $request->time_limit,
            'passing_score' => $request->passing_score,
            'max_attempts' => $request->max_attempts,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return back()->with('success', 'Настройки теста обновлены!');
    }

    /**
     * Переключить статус теста (активен/неактивен)
     */
    public function toggleStatus(Test $test)
    {
        // Проверяем доступ
        $this->authorize('update', $test);

        $test->update([
            'is_active' => !$test->is_active
        ]);

        return back()->with('success', 'Статус теста изменен!');
    }
}
