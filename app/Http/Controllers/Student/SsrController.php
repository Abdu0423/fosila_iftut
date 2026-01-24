<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SsrTask;
use App\Models\SsrTopic;
use App\Models\SsrSubmission;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SsrController extends Controller
{
    /**
     * Список всех доступных ССР для студента
     */
    public function index()
    {
        $student = Auth::user();

        // Получаем группу студента
        $groupId = $student->group_id;

        // Получаем ССР, доступные для группы студента
        $tasks = SsrTask::where('is_active', true)
            ->whereHas('schedule', function ($query) use ($groupId) {
                $query->where('group_id', $groupId);
            })
            ->with(['schedule.subject', 'schedule.group'])
            ->withCount(['topics', 'submissions'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($task) use ($student) {
                // Проверяем, выбрал ли студент тему
                $selectedTopic = $task->topics()
                    ->where('taken_by_student_id', $student->id)
                    ->first();

                // Получаем работу студента
                $submission = $task->submissions()
                    ->where('student_id', $student->id)
                    ->first();

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'deadline' => $task->deadline ? $task->deadline->format('d.m.Y') : null,
                    'is_overdue' => $task->deadline && $task->deadline->isPast(),
                    'schedule' => $task->schedule ? [
                        'subject' => $task->schedule->subject ? $task->schedule->subject->name : null,
                        'group' => $task->schedule->group ? $task->schedule->group->name : null,
                    ] : null,
                    'topics_count' => $task->topics_count,
                    'available_topics_count' => $task->available_topics_count,
                    'has_selected_topic' => (bool) $selectedTopic,
                    'selected_topic' => $selectedTopic ? [
                        'id' => $selectedTopic->id,
                        'topic_text' => $selectedTopic->topic_text,
                    ] : null,
                    'submission' => $submission ? [
                        'id' => $submission->id,
                        'status' => $submission->status,
                        'grade' => $submission->grade,
                    ] : null,
                    'created_at' => $task->created_at->format('d.m.Y'),
                ];
            });

        return Inertia::render('Student/Ssr/Index', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Просмотр ССР и выбор темы
     */
    public function show(SsrTask $task)
    {
        $student = Auth::user();

        // Проверяем доступ (группа студента)
        if ($task->schedule && $task->schedule->group_id !== $student->group_id) {
            abort(403, 'У вас нет доступа к этому заданию');
        }

        if (!$task->is_active) {
            abort(403, 'Это задание неактивно');
        }

        $task->load(['schedule.subject', 'topics.student']);

        // Проверяем, выбрал ли студент тему
        $selectedTopic = $task->topics()
            ->where('taken_by_student_id', $student->id)
            ->first();

        // Получаем работу студента
        $submission = $task->submissions()
            ->where('student_id', $student->id)
            ->first();

        // Форматируем темы
        $topics = $task->topics->map(function ($topic) use ($student) {
            return [
                'id' => $topic->id,
                'topic_text' => $topic->topic_text,
                'is_taken' => $topic->isTaken(),
                'is_mine' => $topic->taken_by_student_id === $student->id,
                'taken_by' => $topic->student && $topic->taken_by_student_id !== $student->id 
                    ? 'Занята' 
                    : null,
            ];
        });

        return Inertia::render('Student/Ssr/Show', [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'requirements' => $task->requirements,
                'deadline' => $task->deadline ? $task->deadline->format('d.m.Y') : null,
                'is_overdue' => $task->deadline && $task->deadline->isPast(),
                'schedule' => $task->schedule ? [
                    'subject' => $task->schedule->subject ? $task->schedule->subject->name : null,
                    'group' => $task->schedule->group ? $task->schedule->group->name : null,
                ] : null,
            ],
            'topics' => $topics,
            'selectedTopic' => $selectedTopic ? [
                'id' => $selectedTopic->id,
                'topic_text' => $selectedTopic->topic_text,
            ] : null,
            'submission' => $submission ? [
                'id' => $submission->id,
                'text' => $submission->text,
                'file_name' => $submission->file_name,
                'teacher_comment' => $submission->teacher_comment,
                'grade' => $submission->grade,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at ? $submission->submitted_at->format('d.m.Y H:i') : null,
                'checked_at' => $submission->checked_at ? $submission->checked_at->format('d.m.Y H:i') : null,
            ] : null,
        ]);
    }

    /**
     * Выбрать тему (AJAX)
     */
    public function selectTopic(Request $request, SsrTask $task)
    {
        $student = Auth::user();

        // Проверяем доступ
        if ($task->schedule && $task->schedule->group_id !== $student->group_id) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет доступа к этому заданию'
            ], 403);
        }

        if (!$task->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Это задание неактивно'
            ], 403);
        }

        $request->validate([
            'topic_id' => 'required|exists:ssr_topics,id',
        ]);

        // Проверяем, что тема принадлежит этому заданию
        $topic = SsrTopic::where('id', $request->topic_id)
            ->where('ssr_task_id', $task->id)
            ->first();

        if (!$topic) {
            return response()->json([
                'success' => false,
                'message' => 'Тема не найдена'
            ], 404);
        }

        try {
            // Используем транзакцию с блокировкой
            SsrTopic::selectTopic($topic->id, $student->id);

            // Создаем запись работы (черновик)
            SsrSubmission::create([
                'ssr_task_id' => $task->id,
                'topic_id' => $topic->id,
                'student_id' => $student->id,
                'status' => 'draft',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Тема успешно выбрана!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 409);
        }
    }

    /**
     * Сохранить работу (черновик)
     */
    public function saveWork(Request $request, SsrTask $task)
    {
        $student = Auth::user();

        $request->validate([
            'text' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);

        // Получаем работу студента
        $submission = $task->submissions()
            ->where('student_id', $student->id)
            ->first();

        if (!$submission) {
            return response()->json([
                'success' => false,
                'message' => 'Сначала выберите тему'
            ], 400);
        }

        if ($submission->isChecked()) {
            return response()->json([
                'success' => false,
                'message' => 'Работа уже проверена и не может быть изменена'
            ], 400);
        }

        $updateData = [
            'text' => $request->text,
        ];

        // Обработка файла
        if ($request->hasFile('file')) {
            // Удаляем старый файл
            if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
                Storage::disk('public')->delete($submission->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('ssr_submissions', $fileName, 'public');

            $updateData['file_path'] = $filePath;
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_size'] = $file->getSize();
        }

        $submission->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Работа сохранена!'
        ]);
    }

    /**
     * Отправить работу на проверку
     */
    public function submitWork(Request $request, SsrTask $task)
    {
        $student = Auth::user();

        $request->validate([
            'text' => 'nullable|string',
        ]);

        // Получаем работу студента
        $submission = $task->submissions()
            ->where('student_id', $student->id)
            ->first();

        if (!$submission) {
            return response()->json([
                'success' => false,
                'message' => 'Сначала выберите тему'
            ], 400);
        }

        if ($submission->isChecked()) {
            return response()->json([
                'success' => false,
                'message' => 'Работа уже проверена'
            ], 400);
        }

        // Проверяем, что есть текст или файл
        $text = $request->text ?? $submission->text;
        if (empty($text) && empty($submission->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Работа не может быть пустой. Напишите текст или прикрепите файл.'
            ], 400);
        }

        // Обновляем текст если передан
        if ($request->has('text')) {
            $submission->text = $request->text;
        }

        $submission->submit();

        return response()->json([
            'success' => true,
            'message' => 'Работа отправлена на проверку!'
        ]);
    }

    /**
     * Удалить файл из работы
     */
    public function deleteFile(SsrTask $task)
    {
        $student = Auth::user();

        $submission = $task->submissions()
            ->where('student_id', $student->id)
            ->first();

        if (!$submission) {
            return response()->json([
                'success' => false,
                'message' => 'Работа не найдена'
            ], 404);
        }

        if ($submission->isSubmitted() && !$submission->isReturned()) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить файл из отправленной работы'
            ], 400);
        }

        if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
            Storage::disk('public')->delete($submission->file_path);
        }

        $submission->update([
            'file_path' => null,
            'file_name' => null,
            'file_size' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Файл удален'
        ]);
    }
}
