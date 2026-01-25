<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\CourseworkTask;
use App\Models\CourseworkTopic;
use App\Models\CourseworkSubmission;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseworkController extends Controller
{
    /**
     * Список курсовых работ учителя
     */
    public function index()
    {
        $teacher = Auth::user();

        // Получаем расписания с курсовыми работами
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->where('has_coursework', true)
            ->with(['subject', 'group', 'courseworkTask.topics', 'courseworkTask.submissions'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($schedule) {
                $task = $schedule->courseworkTask;
                return [
                    'id' => $schedule->id,
                    'subject' => $schedule->subject ? $schedule->subject->name : null,
                    'group' => $schedule->group ? $schedule->group->name : null,
                    'semester' => $schedule->semester,
                    'study_year' => $schedule->study_year,
                    'has_task' => (bool) $task,
                    'task' => $task ? [
                        'id' => $task->id,
                        'title' => $task->title,
                        'deadline' => $task->deadline ? $task->deadline->format('d.m.Y') : null,
                        'is_active' => $task->is_active,
                        'topics_count' => $task->topics->count(),
                        'taken_topics_count' => $task->topics->whereNotNull('taken_by_student_id')->count(),
                        'submissions_count' => $task->submissions->count(),
                        'checked_count' => $task->submissions->where('status', 'checked')->count(),
                    ] : null,
                ];
            });

        return Inertia::render('Teacher/Coursework/Index', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Показать/создать курсовую работу для расписания
     */
    public function show(Schedule $schedule)
    {
        $teacher = Auth::user();

        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        if (!$schedule->has_coursework) {
            abort(403, 'Для этого расписания курсовая работа не активирована');
        }

        $schedule->load(['subject', 'group']);

        // Получаем или создаем задание курсовой работы
        $task = $schedule->courseworkTask;

        if (!$task) {
            // Если задания нет, показываем форму создания
            return Inertia::render('Teacher/Coursework/Create', [
                'schedule' => [
                    'id' => $schedule->id,
                    'subject' => $schedule->subject ? $schedule->subject->name : null,
                    'group' => $schedule->group ? $schedule->group->name : null,
                    'semester' => $schedule->semester,
                    'study_year' => $schedule->study_year,
                ],
            ]);
        }

        $task->load(['topics.student', 'topics.submission']);

        // Форматируем темы
        $topics = $task->topics->map(function ($topic) {
            return [
                'id' => $topic->id,
                'topic_text' => $topic->topic_text,
                'is_taken' => $topic->isTaken(),
                'taken_at' => $topic->taken_at ? $topic->taken_at->format('d.m.Y H:i') : null,
                'student' => $topic->student ? [
                    'id' => $topic->student->id,
                    'name' => $topic->student->name,
                    'last_name' => $topic->student->last_name,
                    'full_name' => ($topic->student->last_name ?? '') . ' ' . ($topic->student->name ?? ''),
                ] : null,
                'submission' => $topic->submission ? [
                    'id' => $topic->submission->id,
                    'status' => $topic->submission->status,
                    'grade_100' => $topic->submission->grade_100,
                    'grade_10' => $topic->submission->grade_10,
                    'grade_letter' => $topic->submission->grade_letter,
                    'submitted_at' => $topic->submission->submitted_at ? $topic->submission->submitted_at->format('d.m.Y H:i') : null,
                    'checked_at' => $topic->submission->checked_at ? $topic->submission->checked_at->format('d.m.Y H:i') : null,
                ] : null,
            ];
        });

        return Inertia::render('Teacher/Coursework/Show', [
            'schedule' => [
                'id' => $schedule->id,
                'subject' => $schedule->subject ? $schedule->subject->name : null,
                'group' => $schedule->group ? $schedule->group->name : null,
                'semester' => $schedule->semester,
                'study_year' => $schedule->study_year,
            ],
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'requirements' => $task->requirements,
                'deadline' => $task->deadline ? $task->deadline->format('d.m.Y') : null,
                'deadline_raw' => $task->deadline ? $task->deadline->format('Y-m-d') : null,
                'is_active' => $task->is_active,
            ],
            'topics' => $topics,
        ]);
    }

    /**
     * Сохранить курсовую работу
     */
    public function store(Request $request, Schedule $schedule)
    {
        $teacher = Auth::user();

        if ($schedule->teacher_id !== $teacher->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'deadline' => 'nullable|date',
            'topics' => 'required|array|min:1',
            'topics.*' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $schedule) {
            // Создаем или обновляем задание
            $task = CourseworkTask::updateOrCreate(
                ['schedule_id' => $schedule->id],
                [
                    'title' => $request->title,
                    'description' => $request->description,
                    'requirements' => $request->requirements,
                    'deadline' => $request->deadline,
                    'is_active' => true,
                ]
            );

            // Создаем темы
            foreach ($request->topics as $topicText) {
                if (trim($topicText)) {
                    CourseworkTopic::create([
                        'coursework_task_id' => $task->id,
                        'topic_text' => trim($topicText),
                    ]);
                }
            }
        });

        return redirect()->route('teacher.coursework.show', $schedule)
            ->with('success', 'Курсовая работа успешно создана!');
    }

    /**
     * Обновить курсовую работу
     */
    public function update(Request $request, Schedule $schedule)
    {
        $teacher = Auth::user();

        if ($schedule->teacher_id !== $teacher->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'deadline' => 'nullable|date',
            'is_active' => 'boolean',
            'topics' => 'required|array|min:1',
            'topics.*.id' => 'nullable|exists:coursework_topics,id',
            'topics.*.topic_text' => 'required|string|max:500',
        ]);

        $task = $schedule->courseworkTask;

        if (!$task) {
            abort(404);
        }

        DB::transaction(function () use ($request, $task) {
            // Обновляем задание
            $task->update([
                'title' => $request->title,
                'description' => $request->description,
                'requirements' => $request->requirements,
                'deadline' => $request->deadline,
                'is_active' => $request->is_active ?? true,
            ]);

            // Получаем ID существующих тем
            $existingTopicIds = collect($request->topics)
                ->pluck('id')
                ->filter()
                ->toArray();

            // Удаляем темы, которых нет в запросе (только незанятые)
            $task->topics()
                ->whereNull('taken_by_student_id')
                ->whereNotIn('id', $existingTopicIds)
                ->delete();

            // Обновляем/создаем темы
            foreach ($request->topics as $topicData) {
                if (!empty($topicData['id'])) {
                    $topic = CourseworkTopic::find($topicData['id']);
                    if ($topic && $topic->coursework_task_id === $task->id && !$topic->isTaken()) {
                        $topic->update(['topic_text' => trim($topicData['topic_text'])]);
                    }
                } else {
                    if (trim($topicData['topic_text'])) {
                        CourseworkTopic::create([
                            'coursework_task_id' => $task->id,
                            'topic_text' => trim($topicData['topic_text']),
                        ]);
                    }
                }
            }
        });

        return back()->with('success', 'Курсовая работа успешно обновлена!');
    }

    /**
     * Просмотр работы студента
     */
    public function showSubmission(CourseworkSubmission $submission)
    {
        $teacher = Auth::user();

        if ($submission->courseworkTask->schedule->teacher_id !== $teacher->id) {
            abort(403);
        }

        $submission->load(['topic', 'student', 'courseworkTask.schedule.subject', 'courseworkTask.schedule.group']);

        return Inertia::render('Teacher/Coursework/Submission', [
            'submission' => [
                'id' => $submission->id,
                'text' => $submission->text,
                'file_path' => $submission->file_path,
                'file_name' => $submission->file_name,
                'file_size' => $submission->file_size_formatted,
                'teacher_comment' => $submission->teacher_comment,
                'grade_100' => $submission->grade_100,
                'grade_10' => $submission->grade_10,
                'grade_letter' => $submission->grade_letter,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at ? $submission->submitted_at->format('d.m.Y H:i') : null,
                'checked_at' => $submission->checked_at ? $submission->checked_at->format('d.m.Y H:i') : null,
            ],
            'topic' => [
                'id' => $submission->topic->id,
                'topic_text' => $submission->topic->topic_text,
            ],
            'student' => [
                'id' => $submission->student->id,
                'name' => $submission->student->name,
                'last_name' => $submission->student->last_name,
                'full_name' => ($submission->student->last_name ?? '') . ' ' . ($submission->student->name ?? ''),
            ],
            'schedule' => [
                'id' => $submission->courseworkTask->schedule->id,
                'subject' => $submission->courseworkTask->schedule->subject ? $submission->courseworkTask->schedule->subject->name : null,
                'group' => $submission->courseworkTask->schedule->group ? $submission->courseworkTask->schedule->group->name : null,
            ],
        ]);
    }

    /**
     * Проверить работу студента
     */
    public function checkSubmission(Request $request, CourseworkSubmission $submission)
    {
        $teacher = Auth::user();

        if ($submission->courseworkTask->schedule->teacher_id !== $teacher->id) {
            abort(403);
        }

        $request->validate([
            'grade_100' => 'required|numeric|min:0|max:100',
            'teacher_comment' => 'nullable|string',
            'action' => 'required|in:check,return',
        ]);

        if ($request->action === 'check') {
            $submission->check($request->grade_100, $request->teacher_comment);
            $message = 'Работа проверена и оценена!';
        } else {
            $submission->returnForRevision($request->teacher_comment);
            $message = 'Работа возвращена на доработку!';
        }

        return back()->with('success', $message);
    }
}
