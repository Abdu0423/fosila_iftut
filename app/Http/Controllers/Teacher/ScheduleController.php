<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Syllabus;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * Показать расписание текущего учителя
     */
    public function index()
    {
        $teacher = Auth::user();
        
        // Получаем расписания текущего учителя с подсчетом тестов
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['subject', 'group', 'syllabuses.creator', 'lessons.subject'])
            ->withCount('tests')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'subject' => $schedule->subject ? [
                        'id' => $schedule->subject->id,
                        'name' => $schedule->subject->name,
                    ] : null,
                    'group' => $schedule->group ? [
                        'id' => $schedule->group->id,
                        'name' => $schedule->group->name,
                    ] : null,
                    'scheduled_at' => $schedule->scheduled_at ? $schedule->scheduled_at->format('Y-m-d H:i:s') : null,
                    'scheduled_at_formatted' => $schedule->scheduled_at ? $schedule->scheduled_at->format('d.m.Y H:i') : 'Не указано',
                    'semester' => $schedule->semester,
                    'study_year' => $schedule->study_year,
                    'credits' => $schedule->credits,
                    'is_active' => $schedule->is_active,
                    'syllabuses' => $schedule->syllabuses->map(function ($syllabus) {
                        return [
                            'id' => $syllabus->id,
                            'name' => $syllabus->name,
                            'description' => $syllabus->description,
                            'file_name' => $syllabus->file_name,
                            'file_size_formatted' => $syllabus->file_size_formatted ?? 'Не указано',
                            'creator' => $syllabus->creator ? [
                                'id' => $syllabus->creator->id,
                                'name' => $syllabus->creator->name,
                            ] : null,
                        ];
                    }),
                    'lessons' => $schedule->lessons->map(function ($lesson) {
                        return [
                            'id' => $lesson->id,
                            'title' => $lesson->title,
                            'description' => $lesson->description,
                            'subject' => $lesson->subject ? [
                                'id' => $lesson->subject->id,
                                'name' => $lesson->subject->name,
                            ] : null,
                            'pivot' => [
                                'order' => $lesson->pivot->order ?? null,
                                'start_time' => $lesson->pivot->start_time ?? null,
                                'end_time' => $lesson->pivot->end_time ?? null,
                                'room' => $lesson->pivot->room ?? null,
                            ],
                        ];
                    }),
                    'tests_count' => $schedule->tests_count ?? 0,
                    'created_at' => $schedule->created_at->format('d.m.Y H:i'),
                ];
            });

        // Получаем доступные силлабусы для добавления
        $availableSyllabuses = Syllabus::with(['subject', 'creator'])
            ->orderBy('name')
            ->get()
            ->map(function ($syllabus) {
                return [
                    'id' => $syllabus->id,
                    'name' => $syllabus->name,
                    'description' => $syllabus->description,
                    'subject' => $syllabus->subject ? [
                        'id' => $syllabus->subject->id,
                        'name' => $syllabus->subject->name,
                    ] : null,
                ];
            });

        return Inertia::render('Teacher/Schedule/Index', [
            'schedules' => $schedules,
            'availableSyllabuses' => $availableSyllabuses,
        ]);
    }

    /**
     * Создать новое расписание
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'group_id' => 'required|exists:groups,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string|max:1000',
        ]);

        $teacher = Auth::user();

        $schedule = Schedule::create([
            'subject_id' => $request->subject_id,
            'teacher_id' => $teacher->id,
            'group_id' => $request->group_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('teacher.schedule.index')
            ->with('success', 'Расписание создано успешно!');
    }

    /**
     * Показать конкретное расписание
     */
    public function show(Schedule $schedule)
    {
        $teacher = Auth::user();
        
        // Проверяем, что расписание принадлежит текущему учителю
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        $schedule->load(['subject', 'group', 'syllabuses.creator', 'lessons.subject', 'test']);

        // Получаем доступные предметы для редактирования
        $subjects = Subject::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Получаем доступные группы
        $groups = Group::where('status', 'active')
            ->orderBy('name')
            ->get();

        // Получаем доступные силлабусы для добавления
        $availableSyllabuses = Syllabus::with(['subject', 'creator'])
            ->whereNotIn('id', $schedule->syllabuses->pluck('id'))
            ->orderBy('name')
            ->get();

        // Получаем доступные уроки для добавления
        $availableLessons = Lesson::with(['subject'])
            ->whereNotIn('id', $schedule->lessons->pluck('id'))
            ->orderBy('title')
            ->get();

        return Inertia::render('Teacher/Schedule/Show', [
            'schedule' => $schedule,
            'subjects' => $subjects,
            'groups' => $groups,
            'availableSyllabuses' => $availableSyllabuses,
            'availableLessons' => $availableLessons,
        ]);
    }


    /**
     * Обновить расписание
     */
    public function update(Request $request, Schedule $schedule)
    {
        $teacher = Auth::user();
        
        // Проверяем, что расписание принадлежит текущему учителю
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'group_id' => 'required|exists:groups,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $schedule->update($request->all());

        return redirect()->route('teacher.schedule.index')
            ->with('success', 'Расписание обновлено успешно!');
    }

    /**
     * Удалить расписание
     */
    public function destroy(Schedule $schedule)
    {
        $teacher = Auth::user();
        
        // Проверяем, что расписание принадлежит текущему учителю
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        $schedule->delete();

        return redirect()->route('teacher.schedule.index')
            ->with('success', 'Расписание удалено успешно!');
    }

    /**
     * Добавить силлабус к расписанию
     */
    public function addSyllabus(Request $request, Schedule $schedule)
    {
        $teacher = Auth::user();
        
        // Проверяем, что расписание принадлежит текущему учителю
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        $request->validate([
            'syllabus_ids' => 'required|array',
            'syllabus_ids.*' => 'exists:syllabuses,id',
        ]);

        // Добавляем силлабусы к расписанию
        $schedule->syllabuses()->sync($request->syllabus_ids);

        return response()->json([
            'success' => true,
            'message' => 'Силлабусы добавлены к расписанию'
        ]);
    }

    /**
     * Загрузить силлабус из файла и добавить к расписанию
     */
    public function uploadSyllabus(Request $request, Schedule $schedule)
    {
        $teacher = Auth::user();
        
        // Проверяем, что расписание принадлежит текущему учителю
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,txt,jpg,jpeg,png,gif,bmp,webp,md,html,css,js,json,xml,csv,log|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('syllabuses', $fileName, 'public');

            // Создаем новый силлабус
            $syllabus = Syllabus::create([
                'name' => $request->name,
                'description' => $request->description ?? null,
                'subject_id' => $schedule->subject_id,
                'creation_year' => date('Y'),
                'created_by' => $teacher->id,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            // Привязываем силлабус к расписанию
            $schedule->syllabuses()->attach($syllabus->id);

            return response()->json([
                'success' => true,
                'message' => 'Силлабус загружен и добавлен к расписанию'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке силлабуса: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Добавить урок к расписанию
     */
    public function addLesson(Request $request, Schedule $schedule)
    {
        $teacher = Auth::user();
        
        // Проверяем, что расписание принадлежит текущему учителю
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        $request->validate([
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'exists:lessons,id',
        ]);

        // Получаем текущий максимальный порядок
        $maxOrder = $schedule->lessons()->max('lesson_schedule.order') ?? 0;

        // Добавляем уроки к расписанию с порядком
        $lessonData = [];
        foreach ($request->lesson_ids as $index => $lessonId) {
            $lessonData[$lessonId] = [
                'order' => $maxOrder + $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $schedule->lessons()->sync($lessonData);

        return response()->json([
            'success' => true,
            'message' => 'Уроки добавлены к расписанию'
        ]);
    }

    /**
     * Удалить силлабус из расписания
     */
    public function removeSyllabus(Request $request, Schedule $schedule, Syllabus $syllabus)
    {
        $teacher = Auth::user();
        
        // Проверяем, что расписание принадлежит текущему учителю
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        $syllabusName = $syllabus->name;
        $schedule->syllabuses()->detach($syllabus->id);

        // Если запрос через AJAX (Inertia), возвращаем редирект с flash сообщением
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Силлабус удален из расписания'
            ]);
        }

        return redirect()->route('teacher.schedule.index')
            ->with('success', "Силлабус \"{$syllabusName}\" успешно удален из расписания");
    }

    /**
     * Удалить урок из расписания
     */
    public function removeLesson(Schedule $schedule, Lesson $lesson)
    {
        $teacher = Auth::user();
        
        // Проверяем, что расписание принадлежит текущему учителю
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        $schedule->lessons()->detach($lesson->id);

        return response()->json([
            'success' => true,
            'message' => 'Урок удален из расписания'
        ]);
    }

    /**
     * Изменить порядок уроков в расписании
     */
    public function reorderLessons(Request $request, Schedule $schedule)
    {
        $teacher = Auth::user();
        
        // Проверяем, что расписание принадлежит текущему учителю
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        $request->validate([
            'lesson_orders' => 'required|array',
            'lesson_orders.*.lesson_id' => 'required|exists:lessons,id',
            'lesson_orders.*.order' => 'required|integer|min:1',
        ]);

        foreach ($request->lesson_orders as $item) {
            $schedule->lessons()->updateExistingPivot($item['lesson_id'], [
                'order' => $item['order']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Порядок уроков обновлен'
        ]);
    }
}