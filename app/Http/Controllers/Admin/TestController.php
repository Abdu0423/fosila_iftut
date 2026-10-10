<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\Lesson;
use App\Models\User;

class TestController extends Controller
{
    /**
     * Показать список тестов
     */
    public function index()
    {
        $tests = Test::with(['lesson', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($test) {
                return [
                    'id' => $test->id,
                    'title' => $test->title,
                    'description' => $test->description,
                    'lesson_name' => $test->lesson->name ?? 'Не указан',
                    'creator_name' => $test->creator->name ?? 'Не указан',
                    'type' => $test->type,
                    'type_text' => $this->getTypeText($test->type),
                    'status' => $test->status,
                    'time_limit' => $test->time_limit,
                    'passing_score' => $test->passing_score,
                    'max_attempts' => $test->max_attempts,
                    'is_active' => $test->is_active,
                    'is_public' => $test->is_public,
                    'start_date' => $test->start_date ? $test->start_date->format('d.m.Y H:i') : null,
                    'end_date' => $test->end_date ? $test->end_date->format('d.m.Y H:i') : null,
                    'created_at' => $test->created_at->format('d.m.Y H:i'),
                    'questions_count' => $test->questions()->count(),
                ];
            });

        $lessons = Lesson::all()->map(function ($lesson) {
            return [
                'id' => $lesson->id,
                'title' => $lesson->name,
            ];
        });

        return spa('Admin/Tests/Index', [
            'tests' => $tests,
            'lessons' => $lessons,
            'testTypes' => [
                ['value' => 'quiz', 'text' => 'Тест'],
                ['value' => 'exam', 'text' => 'Экзамен'],
                ['value' => 'homework', 'text' => 'Домашнее задание'],
                ['value' => 'practice', 'text' => 'Практическая работа'],
            ],
            'statuses' => [
                ['value' => 'active', 'text' => 'Активен'],
                ['value' => 'inactive', 'text' => 'Неактивен'],
                ['value' => 'waiting', 'text' => 'Ожидает'],
                ['value' => 'finished', 'text' => 'Завершен'],
            ],
        ]);
    }

    /**
     * Показать форму создания теста
     */
    public function create()
    {
        $lessons = Lesson::all()->map(function ($lesson) {
            return [
                'id' => $lesson->id,
                'name' => $lesson->name,
            ];
        });

        return spa('Admin/Tests/Create', [
            'lessons' => $lessons,
            'testTypes' => [
                ['value' => 'quiz', 'text' => 'Тест'],
                ['value' => 'exam', 'text' => 'Экзамен'],
                ['value' => 'homework', 'text' => 'Домашнее задание'],
                ['value' => 'practice', 'text' => 'Практическая работа'],
            ],
        ]);
    }

    /**
     * Показать детали теста
     */
    public function show(Test $test)
    {
        $test->load(['lesson', 'creator']);

        return spa('Admin/Tests/Show', [
            'test' => [
                'id' => $test->id,
                'title' => $test->title,
                'description' => $test->description,
                'lesson_name' => $test->lesson->name ?? 'Не указан',
                'creator_name' => $test->creator->name ?? 'Не указан',
                'type' => $test->type,
                'time_limit' => $test->time_limit,
                'passing_score' => $test->passing_score,
                'max_attempts' => $test->max_attempts,
                'is_active' => $test->is_active,
                'is_public' => $test->is_public,
                'start_date' => $test->start_date ? $test->start_date->format('d.m.Y H:i') : null,
                'end_date' => $test->end_date ? $test->end_date->format('d.m.Y H:i') : null,
                'created_at' => $test->created_at->format('d.m.Y H:i'),
                'updated_at' => $test->updated_at->format('d.m.Y H:i'),
                'status' => $test->status,
                'questions_count' => $test->questions()->count(),
                'attempts_count' => 0, // Пока моковые данные
                'avg_score' => 0, // Пока моковые данные
                'pass_rate' => 0, // Пока моковые данные
            ],
            'testTypes' => [
                ['value' => 'quiz', 'text' => 'Тест'],
                ['value' => 'exam', 'text' => 'Экзамен'],
                ['value' => 'homework', 'text' => 'Домашнее задание'],
                ['value' => 'practice', 'text' => 'Практическая работа'],
            ],
        ]);
    }

    /**
     * Сохранить новый тест
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lesson_id' => 'required|exists:lessons,id',
            'type' => 'required|in:quiz,exam,homework,practice',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:1|max:100',
            'max_attempts' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $validated['created_by'] = auth()->id();

        Test::create($validated);

        return redirect()->route('admin.tests.index')
            ->with('success', 'Тест успешно создан');
    }

    /**
     * Показать форму редактирования теста
     */
    public function edit(Test $test)
    {
        $test->load(['lesson', 'creator']);

        $lessons = Lesson::all()->map(function ($lesson) {
            return [
                'id' => $lesson->id,
                'name' => $lesson->name,
            ];
        });

        return spa('Admin/Tests/Edit', [
            'test' => [
                'id' => $test->id,
                'title' => $test->title,
                'description' => $test->description,
                'lesson_id' => $test->lesson_id,
                'lesson_name' => $test->lesson->name ?? 'Не указан',
                'creator_name' => $test->creator->name ?? 'Не указан',
                'type' => $test->type,
                'time_limit' => $test->time_limit,
                'passing_score' => $test->passing_score,
                'max_attempts' => $test->max_attempts,
                'is_active' => $test->is_active,
                'is_public' => $test->is_public,
                'start_date' => $test->start_date ? $test->start_date->format('Y-m-d\TH:i') : null,
                'end_date' => $test->end_date ? $test->end_date->format('Y-m-d\TH:i') : null,
                'created_at' => $test->created_at->format('d.m.Y H:i'),
                'updated_at' => $test->updated_at->format('d.m.Y H:i'),
                'status' => $test->status,
                'questions_count' => $test->questions()->count(),
            ],
            'lessons' => $lessons,
            'testTypes' => [
                ['value' => 'quiz', 'text' => 'Тест'],
                ['value' => 'exam', 'text' => 'Экзамен'],
                ['value' => 'homework', 'text' => 'Домашнее задание'],
                ['value' => 'practice', 'text' => 'Практическая работа'],
            ],
        ]);
    }

    /**
     * Обновить тест
     */
    public function update(Request $request, Test $test)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lesson_id' => 'required|exists:lessons,id',
            'type' => 'required|in:quiz,exam,homework,practice',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:1|max:100',
            'max_attempts' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $test->update($validated);

        return redirect()->route('admin.tests.index')
            ->with('success', 'Тест успешно обновлен');
    }

    /**
     * Удалить тест
     */
    public function destroy(Test $test)
    {
        $test->delete();

        return redirect()->route('admin.tests.index')
            ->with('success', 'Тест успешно удален');
    }

    /**
     * Управление вопросами теста
     */
    public function questions(Test $test)
    {
        $test->load(['questions.answers']);

        return spa('Admin/Tests/Questions', [
            'test' => [
                'id' => $test->id,
                'title' => $test->title,
                'lesson_name' => $test->lesson->name ?? 'Не указан',
            ],
            'questions' => $test->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'type' => $question->type,
                    'type_text' => $this->getQuestionTypeText($question->type),
                    'points' => $question->points,
                    'order' => $question->order,
                    'explanation' => $question->explanation,
                    'answers' => $question->answers->map(function ($answer) {
                        return [
                            'id' => $answer->id,
                            'answer' => $answer->answer,
                            'is_correct' => $answer->is_correct,
                            'order' => $answer->order,
                        ];
                    }),
                ];
            }),
            'questionTypes' => [
                ['value' => 'single_choice', 'text' => 'Тест с выбором одного правильного ответа'],
                ['value' => 'multiple_choice', 'text' => 'Тест с выбором нескольких правильных ответов'],
                ['value' => 'matching', 'text' => 'Тест на соответствие'],
            ],
        ]);
    }

    /**
     * Сохранить новый вопрос
     */
    public function storeQuestion(Request $request, Test $test)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:single_choice,multiple_choice,matching',
            'order' => 'nullable|integer|min:1',
            'explanation' => 'nullable|string',
            'answers' => 'required|array|min:3',
            'answers.*.answer' => 'required|string',
            'answers.*.is_correct' => 'boolean',
            'answers.*.order' => 'required|integer|min:1',
            'answers.*.matching_key' => 'nullable|string',
            'answers.*.matching_value' => 'nullable|string',
        ]);

        // Проверяем, что есть хотя бы один правильный ответ
        $hasCorrectAnswer = collect($validated['answers'])->contains('is_correct', true);
        if (!$hasCorrectAnswer) {
            return back()->withErrors(['answers' => 'Должен быть хотя бы один правильный ответ']);
        }

        // Создаем вопрос
        $question = $test->questions()->create([
            'question' => $validated['question'],
            'type' => $validated['type'],
            'order' => $validated['order'] ?? $test->questions()->count() + 1,
            'explanation' => $validated['explanation'],
        ]);

        // Создаем ответы
        foreach ($validated['answers'] as $answerData) {
            $question->answers()->create([
                'answer' => $answerData['answer'],
                'is_correct' => $answerData['is_correct'],
                'order' => $answerData['order'],
                'matching_key' => $answerData['matching_key'] ?? null,
                'matching_value' => $answerData['matching_value'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Вопрос успешно добавлен');
    }

    /**
     * Обновить вопрос
     */
    public function updateQuestion(Request $request, Test $test, $questionId)
    {
        $question = $test->questions()->findOrFail($questionId);

        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:single_choice,multiple_choice,matching',
            'order' => 'nullable|integer|min:1',
            'explanation' => 'nullable|string',
            'answers' => 'required|array|min:3',
            'answers.*.answer' => 'required|string',
            'answers.*.is_correct' => 'boolean',
            'answers.*.order' => 'required|integer|min:1',
            'answers.*.matching_key' => 'nullable|string',
            'answers.*.matching_value' => 'nullable|string',
        ]);

        // Проверяем, что есть хотя бы один правильный ответ
        $hasCorrectAnswer = collect($validated['answers'])->contains('is_correct', true);
        if (!$hasCorrectAnswer) {
            return back()->withErrors(['answers' => 'Должен быть хотя бы один правильный ответ']);
        }

        // Обновляем вопрос
        $question->update([
            'question' => $validated['question'],
            'type' => $validated['type'],
            'order' => $validated['order'] ?? $question->order,
            'explanation' => $validated['explanation'],
        ]);

        // Удаляем старые ответы и создаем новые
        $question->answers()->delete();
        foreach ($validated['answers'] as $answerData) {
            $question->answers()->create([
                'answer' => $answerData['answer'],
                'is_correct' => $answerData['is_correct'],
                'order' => $answerData['order'],
                'matching_key' => $answerData['matching_key'] ?? null,
                'matching_value' => $answerData['matching_value'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Вопрос успешно обновлен');
    }

    /**
     * Удалить вопрос
     */
    public function destroyQuestion(Test $test, $questionId)
    {
        $question = $test->questions()->findOrFail($questionId);
        $question->delete();

        return redirect()->back()->with('success', 'Вопрос успешно удален');
    }

    /**
     * Предварительный просмотр теста
     */
    public function preview(Test $test)
    {
        $test->load(['questions.answers']);

        return spa('Admin/Tests/Preview', [
            'test' => [
                'id' => $test->id,
                'title' => $test->title,
                'description' => $test->description,
                'lesson_name' => $test->lesson->name ?? 'Не указан',
                'type' => $test->type,
                'time_limit' => $test->time_limit,
                'passing_score' => $test->passing_score,
                'max_attempts' => $test->max_attempts,
            ],
            'questions' => $test->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'type' => $question->type,
                    'type_text' => $this->getQuestionTypeText($question->type),
                    'points' => $question->points,
                    'order' => $question->order,
                    'explanation' => $question->explanation,
                    'answers' => $question->answers->map(function ($answer) {
                        return [
                            'id' => $answer->id,
                            'answer' => $answer->answer,
                            'is_correct' => $answer->is_correct,
                            'order' => $answer->order,
                        ];
                    }),
                ];
            }),
            'testTypes' => [
                ['value' => 'quiz', 'text' => 'Тест'],
                ['value' => 'exam', 'text' => 'Экзамен'],
                ['value' => 'homework', 'text' => 'Домашнее задание'],
                ['value' => 'practice', 'text' => 'Практическая работа'],
            ],
        ]);
    }

    /**
     * Получить текст типа теста
     */
    private function getTypeText($type)
    {
        $types = [
            'quiz' => 'Тест',
            'exam' => 'Экзамен',
            'homework' => 'Домашнее задание',
            'practice' => 'Практическая работа',
        ];

        return $types[$type] ?? $type;
    }

    /**
     * Получить текст типа вопроса
     */
    private function getQuestionTypeText($type)
    {
        $types = [
            'single_choice' => 'Тест с выбором одного правильного ответа',
            'multiple_choice' => 'Тест с выбором нескольких правильных ответов',
            'matching' => 'Тест на соответствие',
        ];

        return $types[$type] ?? $type;
    }
}
