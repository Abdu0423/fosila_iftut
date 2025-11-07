<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Group;
use App\Models\User;
use App\Models\Syllabus;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['subject', 'teacher', 'group', 'syllabuses', 'lessons']);
        
        // Поиск по предмету, учителю или группе
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('subject', function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('teacher', function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('group', function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });
            });
        }

        // Фильтр по предмету
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Фильтр по группе
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        // Фильтр по семестру
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        // Фильтр по учебному году
        if ($request->filled('study_year')) {
            $query->where('study_year', $request->study_year);
        }

        // Фильтр по активности
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'scheduled_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $schedules = $query->with(['tests' => function($q) {
            $q->whereIn('exam_type', ['periodic', 'final'])->orderBy('exam_type')->orderBy('id');
        }])->paginate(20)
            ->withQueryString();

        // Данные для фильтров
        $subjects = Subject::all();
        $groups = Group::all();
        $teachers = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->get();

        // Форматируем расписания для отображения дат экзаменов
        $schedules->getCollection()->transform(function($schedule) {
            $periodicExams = $schedule->tests->where('exam_type', 'periodic');
            $finalExam = $schedule->tests->where('exam_type', 'final')->first();
            
            $schedule->periodic_exam_1_date = $periodicExams->first()?->exam_date;
            $schedule->periodic_exam_2_date = $periodicExams->skip(1)->first()?->exam_date;
            $schedule->final_exam_date = $finalExam?->exam_date;
            
            return $schedule;
        });

        return Inertia::render('Admin/Schedules/Index', [
            'schedules' => $schedules,
            'subjects' => $subjects,
            'groups' => $groups,
            'teachers' => $teachers,
            'filters' => [
                'search' => $request->search ?? '',
                'subject_id' => $request->subject_id ?? '',
                'group_id' => $request->group_id ?? '',
                'semester' => $request->semester ?? '',
                'study_year' => $request->study_year ?? '',
                'is_active' => $request->is_active ?? '',
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subjects = Subject::all();
        $groups = Group::all();
        $teachers = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->get();
        $syllabuses = Syllabus::with(['creator'])->get();
        $lessons = Lesson::with(['subject'])->get();

        // \Log::info('Schedule Create - Data loaded', [
        //     'subjects_count' => $subjects->count(),
        //     'groups_count' => $groups->count(),
        //     'teachers_count' => $teachers->count()
        // ]);

        return Inertia::render('Admin/Schedules/Create', [
            'subjects' => $subjects,
            'groups' => $groups,
            'teachers' => $teachers,
            'syllabuses' => $syllabuses,
            'lessons' => $lessons,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
            'semester' => 'required|integer|in:1,2',
            'credits' => 'required|integer|min:1|max:10',
            'study_year' => 'required|integer|min:2020|max:2030',
            'order' => 'required|integer|min:1',
            'scheduled_at' => 'nullable|date',
            'is_active' => 'boolean',
            'syllabus_ids' => 'nullable|array',
            'syllabus_ids.*' => 'exists:syllabuses,id',
            'lesson_ids' => 'nullable|array',
            'lesson_ids.*' => 'exists:lessons,id',
        ]);

        $schedule = Schedule::create($validated);

        // Привязываем силлабусы к расписанию
        if ($request->filled('syllabus_ids')) {
            $schedule->syllabuses()->attach($request->syllabus_ids);
        }

        // Привязываем уроки к расписанию
        if ($request->filled('lesson_ids')) {
            $lessonData = [];
            foreach ($request->lesson_ids as $index => $lessonId) {
                $lessonData[$lessonId] = ['order' => $index + 1];
            }
            $schedule->lessons()->attach($lessonData);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Расписание успешно создано!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        $schedule->load(['subject', 'teacher', 'group', 'syllabuses.creator', 'lessons.subject']);

        return Inertia::render('Admin/Schedules/Show', [
            'schedule' => $schedule
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        $schedule->load(['subject', 'teacher', 'group', 'syllabuses', 'lessons', 'tests' => function($query) {
            $query->whereIn('exam_type', ['periodic', 'final'])->orderBy('exam_type')->orderBy('id');
        }]);
        
        $subjects = Subject::all();
        $groups = Group::all();
        $teachers = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->get();
        $syllabuses = Syllabus::with(['creator'])->get();
        $lessons = Lesson::with(['subject'])->get();

        // Форматируем экзамены для удобного отображения
        $exams = [
            'periodic_exam_1' => $schedule->tests->where('exam_type', 'periodic')->first() ?? null,
            'periodic_exam_2' => $schedule->tests->where('exam_type', 'periodic')->skip(1)->first() ?? null,
            'final_exam' => $schedule->tests->where('exam_type', 'final')->first() ?? null,
        ];

        return Inertia::render('Admin/Schedules/Edit', [
            'schedule' => $schedule,
            'subjects' => $subjects,
            'groups' => $groups,
            'teachers' => $teachers,
            'syllabuses' => $syllabuses,
            'lessons' => $lessons,
            'exams' => $exams,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
            'semester' => 'required|integer|in:1,2',
            'credits' => 'required|integer|min:1|max:10',
            'study_year' => 'required|integer|min:2020|max:2030',
            'order' => 'required|integer|min:1',
            'scheduled_at' => 'nullable|date',
            'is_active' => 'boolean',
            'syllabus_ids' => 'nullable|array',
            'syllabus_ids.*' => 'exists:syllabuses,id',
            'lesson_ids' => 'nullable|array',
            'lesson_ids.*' => 'exists:lessons,id',
            // Даты экзаменов
            'periodic_exam_1_date' => 'nullable|date',
            'periodic_exam_2_date' => 'nullable|date',
            'final_exam_date' => 'nullable|date',
        ]);

        $schedule->update($validated);

        // Обновляем связи с силлабусами
        if ($request->has('syllabus_ids')) {
            $schedule->syllabuses()->sync($request->syllabus_ids);
        }

        // Обновляем связи с уроками
        if ($request->has('lesson_ids')) {
            $lessonData = [];
            foreach ($request->lesson_ids as $index => $lessonId) {
                $lessonData[$lessonId] = ['order' => $index + 1];
            }
            $schedule->lessons()->sync($lessonData);
        }

        // Обновляем даты экзаменов
        $periodicExams = $schedule->tests()->where('exam_type', 'periodic')->orderBy('id')->get();
        $finalExam = $schedule->tests()->where('exam_type', 'final')->first();

        if ($periodicExams->count() > 0 && $request->has('periodic_exam_1_date')) {
            $periodicExams->first()->update(['exam_date' => $request->periodic_exam_1_date]);
        }
        if ($periodicExams->count() > 1 && $request->has('periodic_exam_2_date')) {
            $periodicExams->skip(1)->first()->update(['exam_date' => $request->periodic_exam_2_date]);
        }
        if ($finalExam && $request->has('final_exam_date')) {
            $finalExam->update(['exam_date' => $request->final_exam_date]);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Расписание успешно обновлено!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        try {
            $schedule->delete();

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Расписание успешно удалено!');

        } catch (\Exception $e) {
            \Log::error('Schedule Destroy - Exception occurred', [
                'schedule_id' => $schedule->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Ошибка при удалении расписания: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(Schedule $schedule)
    {
        $schedule->update(['is_active' => !$schedule->is_active]);

        return back()->with('success', 'Статус расписания изменен!');
    }

    /**
     * Perform bulk actions on selected resources.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:schedules,id'
        ]);

        $schedules = Schedule::whereIn('id', $request->ids);

        switch ($request->action) {
            case 'activate':
                $schedules->update(['is_active' => true]);
                $message = 'Выбранные расписания активированы!';
                break;
            case 'deactivate':
                $schedules->update(['is_active' => false]);
                $message = 'Выбранные расписания деактивированы!';
                break;
            case 'delete':
                $schedules->delete();
                $message = 'Выбранные расписания удалены!';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Duplicate the specified resource.
     */
    public function duplicate(Schedule $schedule)
    {
        $newSchedule = $schedule->replicate();
        $newSchedule->scheduled_at = null; // Сбрасываем дату
        $newSchedule->save();

        return back()->with('success', 'Расписание успешно дублировано!');
    }

    /**
     * Export schedules to a file.
     */
    public function export(Request $request)
    {
        // Здесь можно добавить логику экспорта
        return response()->json([
            'success' => true,
            'message' => 'Экспорт будет реализован позже'
        ]);
    }

    /**
     * Show analytics page
     */
    public function analytics()
    {
        return Inertia::render('Admin/Schedules/Analytics', [
            'schedules' => Schedule::with(['subject', 'teacher', 'group'])->get()
        ]);
    }

    /**
     * Show bulk create form
     */
    public function bulkCreate()
    {
        $subjects = Subject::all();
        $groups = Group::all();
        $teachers = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->get();

        return Inertia::render('Admin/Schedules/BulkCreate', [
            'subjects' => $subjects,
            'groups' => $groups,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Store bulk schedules
     */
    public function bulkStore(Request $request)
    {
        // TODO: Implement bulk store functionality
        return redirect()->route('admin.schedules.index')
            ->with('info', 'Массовое создание будет реализовано позже');
    }

    /**
     * Import schedules
     */
    public function import(Request $request)
    {
        // TODO: Implement import functionality
        return back()->with('info', 'Импорт будет реализован позже');
    }

    /**
     * Показать страницу управления уроками в расписании
     */
    public function lessons(Schedule $schedule)
    {
        $schedule->load(['subject', 'teacher', 'group', 'lessons.subject']);
        $lessons = Lesson::with(['subject'])->get();

        return Inertia::render('Admin/Schedules/Lessons', [
            'schedule' => $schedule,
            'lessons' => $lessons
        ]);
    }

    /**
     * Добавить урок в расписание
     */
    public function addLesson(Request $request, Schedule $schedule)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'duration' => 'nullable|integer|min:15|max:240',
            'start_time' => 'nullable|date_format:H:i',
            'room' => 'nullable|string|max:100',
            'order' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        // Проверяем, что урок еще не добавлен в это расписание
        if ($schedule->lessons()->where('lesson_id', $request->lesson_id)->exists()) {
            return response()->json(['error' => 'Урок уже добавлен в это расписание'], 400);
        }

        $order = $request->order ?: ($schedule->lessons()->max('lesson_schedule.order') + 1);

        $schedule->lessons()->attach($request->lesson_id, [
            'duration' => $request->duration,
            'start_time' => $request->start_time,
            'room' => $request->room,
            'order' => $order,
            'notes' => $request->notes
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Удалить урок из расписания
     */
    public function removeLesson(Schedule $schedule, Lesson $lesson)
    {
        $schedule->lessons()->detach($lesson->id);
        return response()->json(['success' => true]);
    }

    /**
     * Изменить порядок уроков в расписании
     */
    public function reorderLessons(Request $request, Schedule $schedule)
    {
        $request->validate([
            'lessons' => 'required|array',
            'lessons.*.lesson_id' => 'required|exists:lessons,id',
            'lessons.*.order' => 'required|integer|min:1'
        ]);

        foreach ($request->lessons as $lessonData) {
            $schedule->lessons()->updateExistingPivot($lessonData['lesson_id'], [
                'order' => $lessonData['order']
            ]);
        }

        return response()->json(['success' => true]);
    }
}