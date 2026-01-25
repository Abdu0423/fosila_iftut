<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\CourseworkTask;
use App\Models\CourseworkTopic;
use App\Models\CourseworkSubmission;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseworkController extends Controller
{
    /**
     * Список курсовых работ для студента
     */
    public function index()
    {
        $student = Auth::user();
        $groupId = $student->group_id;

        // Получаем расписания с курсовыми работами для группы студента
        $schedules = Schedule::where('group_id', $groupId)
            ->where('has_coursework', true)
            ->whereHas('courseworkTask', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['subject', 'courseworkTask.topics', 'courseworkTask.submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->get()
            ->map(function ($schedule) use ($student) {
                $task = $schedule->courseworkTask;
                
                // Проверяем, выбрал ли студент тему
                $selectedTopic = $task->topics()
                    ->where('taken_by_student_id', $student->id)
                    ->first();
                
                // Получаем работу студента
                $submission = $task->submissions->first();

                return [
                    'id' => $schedule->id,
                    'subject' => $schedule->subject ? $schedule->subject->name : null,
                    'semester' => $schedule->semester,
                    'task' => [
                        'id' => $task->id,
                        'title' => $task->title,
                        'deadline' => $task->deadline ? $task->deadline->format('d.m.Y') : null,
                        'is_overdue' => $task->deadline && $task->deadline->isPast(),
                        'topics_count' => $task->topics->count(),
                        'available_topics_count' => $task->available_topics_count,
                    ],
                    'has_selected_topic' => (bool) $selectedTopic,
                    'selected_topic' => $selectedTopic ? [
                        'id' => $selectedTopic->id,
                        'topic_text' => $selectedTopic->topic_text,
                    ] : null,
                    'submission' => $submission ? [
                        'id' => $submission->id,
                        'status' => $submission->status,
                        'grade_100' => $submission->grade_100,
                        'grade_10' => $submission->grade_10,
                        'grade_letter' => $submission->grade_letter,
                    ] : null,
                ];
            });

        return Inertia::render('Student/Coursework/Index', [
            'courseworks' => $schedules,
        ]);
    }

    /**
     * Просмотр курсовой работы
     */
    public function show(Schedule $schedule)
    {
        $student = Auth::user();

        // Проверяем доступ
        if ($schedule->group_id !== $student->group_id) {
            abort(403);
        }

        if (!$schedule->has_coursework) {
            abort(404);
        }

        $task = $schedule->courseworkTask;

        if (!$task || !$task->is_active) {
            abort(404);
        }

        $schedule->load('subject');
        $task->load('topics.student');

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
            ];
        });

        return Inertia::render('Student/Coursework/Show', [
            'schedule' => [
                'id' => $schedule->id,
                'subject' => $schedule->subject ? $schedule->subject->name : null,
            ],
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'requirements' => $task->requirements,
                'deadline' => $task->deadline ? $task->deadline->format('d.m.Y') : null,
                'is_overdue' => $task->deadline && $task->deadline->isPast(),
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
                'grade_100' => $submission->grade_100,
                'grade_10' => $submission->grade_10,
                'grade_letter' => $submission->grade_letter,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at ? $submission->submitted_at->format('d.m.Y H:i') : null,
                'checked_at' => $submission->checked_at ? $submission->checked_at->format('d.m.Y H:i') : null,
            ] : null,
        ]);
    }

    /**
     * Выбрать тему
     */
    public function selectTopic(Request $request, Schedule $schedule)
    {
        $student = Auth::user();

        if ($schedule->group_id !== $student->group_id) {
            return response()->json(['success' => false, 'message' => 'Нет доступа'], 403);
        }

        $task = $schedule->courseworkTask;

        if (!$task || !$task->is_active) {
            return response()->json(['success' => false, 'message' => 'Курсовая работа не найдена'], 404);
        }

        $request->validate([
            'topic_id' => 'required|exists:coursework_topics,id',
        ]);

        $topic = CourseworkTopic::where('id', $request->topic_id)
            ->where('coursework_task_id', $task->id)
            ->first();

        if (!$topic) {
            return response()->json(['success' => false, 'message' => 'Тема не найдена'], 404);
        }

        try {
            CourseworkTopic::selectTopic($topic->id, $student->id);

            // Создаем запись работы (черновик)
            CourseworkSubmission::create([
                'coursework_task_id' => $task->id,
                'topic_id' => $topic->id,
                'student_id' => $student->id,
                'status' => 'draft',
            ]);

            return response()->json(['success' => true, 'message' => 'Тема успешно выбрана!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }
    }

    /**
     * Сохранить работу
     */
    public function saveWork(Request $request, Schedule $schedule)
    {
        $student = Auth::user();

        $request->validate([
            'text' => 'nullable|string',
            'file' => 'nullable|file|max:20480', // 20MB
        ]);

        $task = $schedule->courseworkTask;
        $submission = $task->submissions()
            ->where('student_id', $student->id)
            ->first();

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Сначала выберите тему'], 400);
        }

        if ($submission->isChecked()) {
            return response()->json(['success' => false, 'message' => 'Работа уже проверена'], 400);
        }

        $updateData = ['text' => $request->text];

        if ($request->hasFile('file')) {
            // Удаляем старый файл
            if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
                Storage::disk('public')->delete($submission->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('coursework_submissions', $fileName, 'public');

            $updateData['file_path'] = $filePath;
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_size'] = $file->getSize();
        }

        $submission->update($updateData);

        return response()->json(['success' => true, 'message' => 'Работа сохранена!']);
    }

    /**
     * Отправить работу
     */
    public function submitWork(Request $request, Schedule $schedule)
    {
        $student = Auth::user();

        $task = $schedule->courseworkTask;
        $submission = $task->submissions()
            ->where('student_id', $student->id)
            ->first();

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Сначала выберите тему'], 400);
        }

        if ($submission->isChecked()) {
            return response()->json(['success' => false, 'message' => 'Работа уже проверена'], 400);
        }

        // Обновляем текст если передан
        if ($request->has('text')) {
            $submission->text = $request->text;
            $submission->save();
        }

        // Проверяем, что есть текст или файл
        if (empty($submission->text) && empty($submission->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Работа не может быть пустой. Напишите текст или прикрепите файл.'
            ], 400);
        }

        $submission->submit();

        return response()->json(['success' => true, 'message' => 'Работа отправлена на проверку!']);
    }

    /**
     * Удалить файл
     */
    public function deleteFile(Schedule $schedule)
    {
        $student = Auth::user();

        $task = $schedule->courseworkTask;
        $submission = $task->submissions()
            ->where('student_id', $student->id)
            ->first();

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Работа не найдена'], 404);
        }

        if ($submission->isSubmitted() && !$submission->isReturned()) {
            return response()->json(['success' => false, 'message' => 'Нельзя удалить файл из отправленной работы'], 400);
        }

        if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
            Storage::disk('public')->delete($submission->file_path);
        }

        $submission->update([
            'file_path' => null,
            'file_name' => null,
            'file_size' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Файл удален']);
    }
}
