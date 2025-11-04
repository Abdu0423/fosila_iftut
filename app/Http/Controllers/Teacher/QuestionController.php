<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestAnswer;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    /**
     * Создать новый вопрос для теста
     */
    public function store(Request $request, Test $test)
    {
        // Проверяем доступ
        $this->authorize('update', $test);
        
        $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:single_choice,multiple_choice,matching',
            'explanation' => 'nullable|string',
            'answers' => 'required|array|min:4',
            'answers.*.answer' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
            'answers.*.matching_key' => 'nullable|string',
            'answers.*.matching_value' => 'nullable|string',
        ], [
            'answers.min' => 'Минимум 4 ответа требуется',
            'answers.required' => 'Необходимо добавить ответы',
        ]);

        DB::transaction(function () use ($request, $test) {
            // Получаем максимальный порядок
            $maxOrder = $test->questions()->max('order') ?? 0;
            
            // Создаем вопрос
            $question = TestQuestion::create([
                'test_id' => $test->id,
                'question' => $request->question,
                'type' => $request->type,
                'order' => $maxOrder + 1,
                'explanation' => $request->explanation,
            ]);

            // Создаем ответы
            foreach ($request->answers as $index => $answerData) {
                TestAnswer::create([
                    'question_id' => $question->id,
                    'answer' => $answerData['answer'],
                    'is_correct' => $answerData['is_correct'],
                    'order' => $index + 1,
                    'matching_key' => $answerData['matching_key'] ?? null,
                    'matching_value' => $answerData['matching_value'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Вопрос успешно создан!');
    }

    /**
     * Обновить вопрос
     */
    public function update(Request $request, Test $test, TestQuestion $question)
    {
        // Проверяем доступ
        $this->authorize('update', $test);
        
        // Проверяем, что вопрос принадлежит тесту
        if ($question->test_id !== $test->id) {
            abort(404, 'Вопрос не найден');
        }

        $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:single_choice,multiple_choice,matching',
            'explanation' => 'nullable|string',
            'answers' => 'required|array|min:4',
            'answers.*.id' => 'nullable|exists:test_answers,id',
            'answers.*.answer' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
            'answers.*.matching_key' => 'nullable|string',
            'answers.*.matching_value' => 'nullable|string',
        ], [
            'answers.min' => 'Минимум 4 ответа требуется',
        ]);

        DB::transaction(function () use ($request, $question) {
            // Обновляем вопрос
            $question->update([
                'question' => $request->question,
                'type' => $request->type,
                'explanation' => $request->explanation,
            ]);

            // Получаем ID существующих ответов
            $existingAnswerIds = collect($request->answers)
                ->pluck('id')
                ->filter()
                ->toArray();

            // Удаляем ответы, которых нет в запросе
            $question->answers()
                ->whereNotIn('id', $existingAnswerIds)
                ->delete();

            // Обновляем или создаем ответы
            foreach ($request->answers as $index => $answerData) {
                if (isset($answerData['id'])) {
                    // Обновляем существующий ответ
                    TestAnswer::where('id', $answerData['id'])
                        ->where('question_id', $question->id)
                        ->update([
                            'answer' => $answerData['answer'],
                            'is_correct' => $answerData['is_correct'],
                            'order' => $index + 1,
                            'matching_key' => $answerData['matching_key'] ?? null,
                            'matching_value' => $answerData['matching_value'] ?? null,
                        ]);
                } else {
                    // Создаем новый ответ
                    TestAnswer::create([
                        'question_id' => $question->id,
                        'answer' => $answerData['answer'],
                        'is_correct' => $answerData['is_correct'],
                        'order' => $index + 1,
                        'matching_key' => $answerData['matching_key'] ?? null,
                        'matching_value' => $answerData['matching_value'] ?? null,
                    ]);
                }
            }
        });

        return back()->with('success', 'Вопрос успешно обновлен!');
    }

    /**
     * Удалить вопрос
     */
    public function destroy(Test $test, TestQuestion $question)
    {
        // Проверяем доступ
        $this->authorize('update', $test);
        
        // Проверяем, что вопрос принадлежит тесту
        if ($question->test_id !== $test->id) {
            abort(404, 'Вопрос не найден');
        }

        $question->delete();

        return back()->with('success', 'Вопрос успешно удален!');
    }

    /**
     * Изменить порядок вопросов
     */
    public function reorder(Request $request, Test $test)
    {
        // Проверяем доступ
        $this->authorize('update', $test);
        
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:test_questions,id',
            'questions.*.order' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $test) {
            foreach ($request->questions as $questionData) {
                TestQuestion::where('id', $questionData['id'])
                    ->where('test_id', $test->id)
                    ->update(['order' => $questionData['order']]);
            }
        });

        return back()->with('success', 'Порядок вопросов обновлен!');
    }
}
