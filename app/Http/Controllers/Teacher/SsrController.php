<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SsrTask;
use App\Models\SsrTopic;
use App\Models\SsrSubmission;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SsrController extends Controller
{
    /**
     * Список всех ССР заданий учителя
     */
    public function index()
    {
        $teacher = Auth::user();

        $tasks = SsrTask::where('teacher_id', $teacher->id)
            ->with(['schedule.subject', 'schedule.group'])
            ->withCount(['topics', 'submissions'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'deadline' => $task->deadline ? $task->deadline->format('d.m.Y') : null,
                    'is_active' => $task->is_active,
                    'schedule' => $task->schedule ? [
                        'id' => $task->schedule->id,
                        'subject' => $task->schedule->subject ? $task->schedule->subject->name : null,
                        'group' => $task->schedule->group ? $task->schedule->group->name : null,
                    ] : null,
                    'topics_count' => $task->topics_count,
                    'available_topics_count' => $task->available_topics_count,
                    'taken_topics_count' => $task->taken_topics_count,
                    'submissions_count' => $task->submissions_count,
                    'created_at' => $task->created_at->format('d.m.Y H:i'),
                ];
            });

        return Inertia::render('Teacher/Ssr/Index', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Форма создания ССР
     */
    public function create()
    {
        $teacher = Auth::user();

        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['subject', 'group'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'label' => ($schedule->group ? $schedule->group->name : 'Нет группы') . ' - ' . 
                               ($schedule->subject ? $schedule->subject->name : 'Нет предмета'),
                    'subject' => $schedule->subject ? $schedule->subject->name : null,
                    'group' => $schedule->group ? $schedule->group->name : null,
                ];
            });

        return Inertia::render('Teacher/Ssr/Create', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Сохранить ССР с темами
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'schedule_id' => 'nullable|exists:schedules,id',
            'deadline' => 'nullable|date',
            'topics' => 'required|array|min:1',
            'topics.*' => 'required|string|max:500',
        ], [
            'topics.required' => 'Добавьте хотя бы одну тему',
            'topics.min' => 'Добавьте хотя бы одну тему',
            'topics.*.required' => 'Тема не может быть пустой',
        ]);

        $teacher = Auth::user();

        // Проверяем, что расписание принадлежит учителю
        if ($request->schedule_id) {
            $schedule = Schedule::where('id', $request->schedule_id)
                ->where('teacher_id', $teacher->id)
                ->first();

            if (!$schedule) {
                return back()->withErrors(['schedule_id' => 'Расписание не найдено']);
            }
        }

        DB::transaction(function () use ($request, $teacher) {
            // Создаем задание
            $task = SsrTask::create([
                'teacher_id' => $teacher->id,
                'schedule_id' => $request->schedule_id,
                'title' => $request->title,
                'description' => $request->description,
                'requirements' => $request->requirements,
                'deadline' => $request->deadline,
                'is_active' => true,
            ]);

            // Создаем темы
            foreach ($request->topics as $topicText) {
                if (trim($topicText)) {
                    SsrTopic::create([
                        'ssr_task_id' => $task->id,
                        'topic_text' => trim($topicText),
                    ]);
                }
            }
        });

        return redirect()->route('teacher.ssr.index')
            ->with('success', 'ССР успешно создана!');
    }

    /**
     * Просмотр ССР с работами студентов
     */
    public function show(SsrTask $task)
    {
        $teacher = Auth::user();

        if ($task->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому заданию');
        }

        $task->load([
            'schedule.subject',
            'schedule.group',
            'topics.student',
            'topics.submission',
        ]);

        // Форматируем темы с информацией о студентах и работах
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
                    'grade' => $topic->submission->grade,
                    'submitted_at' => $topic->submission->submitted_at ? $topic->submission->submitted_at->format('d.m.Y H:i') : null,
                    'checked_at' => $topic->submission->checked_at ? $topic->submission->checked_at->format('d.m.Y H:i') : null,
                ] : null,
            ];
        });

        return Inertia::render('Teacher/Ssr/Show', [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'requirements' => $task->requirements,
                'deadline' => $task->deadline ? $task->deadline->format('d.m.Y') : null,
                'is_active' => $task->is_active,
                'schedule' => $task->schedule ? [
                    'id' => $task->schedule->id,
                    'subject' => $task->schedule->subject ? $task->schedule->subject->name : null,
                    'group' => $task->schedule->group ? $task->schedule->group->name : null,
                ] : null,
                'created_at' => $task->created_at->format('d.m.Y H:i'),
            ],
            'topics' => $topics,
        ]);
    }

    /**
     * Форма редактирования ССР
     */
    public function edit(SsrTask $task)
    {
        $teacher = Auth::user();

        if ($task->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому заданию');
        }

        $task->load('topics');

        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['subject', 'group'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'label' => ($schedule->group ? $schedule->group->name : 'Нет группы') . ' - ' . 
                               ($schedule->subject ? $schedule->subject->name : 'Нет предмета'),
                ];
            });

        return Inertia::render('Teacher/Ssr/Edit', [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'requirements' => $task->requirements,
                'schedule_id' => $task->schedule_id,
                'deadline' => $task->deadline ? $task->deadline->format('Y-m-d') : null,
                'is_active' => $task->is_active,
                'topics' => $task->topics->map(function ($topic) {
                    return [
                        'id' => $topic->id,
                        'topic_text' => $topic->topic_text,
                        'is_taken' => $topic->isTaken(),
                    ];
                }),
            ],
            'schedules' => $schedules,
        ]);
    }

    /**
     * Обновить ССР
     */
    public function update(Request $request, SsrTask $task)
    {
        $teacher = Auth::user();

        if ($task->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому заданию');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'schedule_id' => 'nullable|exists:schedules,id',
            'deadline' => 'nullable|date',
            'is_active' => 'boolean',
            'topics' => 'required|array|min:1',
            'topics.*.id' => 'nullable|exists:ssr_topics,id',
            'topics.*.topic_text' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $task) {
            // Обновляем задание
            $task->update([
                'title' => $request->title,
                'description' => $request->description,
                'requirements' => $request->requirements,
                'schedule_id' => $request->schedule_id,
                'deadline' => $request->deadline,
                'is_active' => $request->is_active ?? true,
            ]);

            // Получаем существующие ID тем
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
                    // Обновляем существующую тему
                    $topic = SsrTopic::find($topicData['id']);
                    if ($topic && $topic->ssr_task_id === $task->id) {
                        // Обновляем только если тема не занята
                        if (!$topic->isTaken()) {
                            $topic->update(['topic_text' => trim($topicData['topic_text'])]);
                        }
                    }
                } else {
                    // Создаем новую тему
                    if (trim($topicData['topic_text'])) {
                        SsrTopic::create([
                            'ssr_task_id' => $task->id,
                            'topic_text' => trim($topicData['topic_text']),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('teacher.ssr.show', $task)
            ->with('success', 'ССР успешно обновлена!');
    }

    /**
     * Удалить ССР
     */
    public function destroy(SsrTask $task)
    {
        $teacher = Auth::user();

        if ($task->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому заданию');
        }

        // Проверяем, есть ли отправленные работы
        $hasSubmissions = $task->submissions()->where('status', '!=', 'draft')->exists();
        if ($hasSubmissions) {
            return back()->withErrors(['error' => 'Невозможно удалить ССР, так как есть отправленные работы']);
        }

        $task->delete();

        return redirect()->route('teacher.ssr.index')
            ->with('success', 'ССР успешно удалена!');
    }

    /**
     * Просмотр работы студента
     */
    public function showSubmission(SsrSubmission $submission)
    {
        $teacher = Auth::user();

        if ($submission->ssrTask->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этой работе');
        }

        $submission->load(['topic', 'student', 'ssrTask']);

        return Inertia::render('Teacher/Ssr/Submission', [
            'submission' => [
                'id' => $submission->id,
                'text' => $submission->text,
                'file_path' => $submission->file_path,
                'file_name' => $submission->file_name,
                'file_size' => $submission->file_size_formatted,
                'teacher_comment' => $submission->teacher_comment,
                'grade' => $submission->grade,
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
            'task' => [
                'id' => $submission->ssrTask->id,
                'title' => $submission->ssrTask->title,
            ],
        ]);
    }

    /**
     * Проверить работу студента
     */
    public function checkSubmission(Request $request, SsrSubmission $submission)
    {
        $teacher = Auth::user();

        if ($submission->ssrTask->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этой работе');
        }

        $request->validate([
            'grade' => 'required|numeric|min:0|max:100',
            'teacher_comment' => 'nullable|string',
            'action' => 'required|in:check,return',
        ]);

        if ($request->action === 'check') {
            $submission->check($request->grade, $request->teacher_comment);
            $message = 'Работа проверена и оценена!';
        } else {
            $submission->returnForRevision($request->teacher_comment);
            $message = 'Работа возвращена на доработку!';
        }

        return back()->with('success', $message);
    }
}
