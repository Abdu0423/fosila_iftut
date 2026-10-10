<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Schedule;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Lesson::with(['subject']);

            // Получаем фильтры из сессии или запроса
            $search = $request->get('search', session('lessons_search'));
            $subjectId = $request->get('subject_id', session('lessons_subject_id'));
            $page = $request->get('page', 1);
            $sortBy = $request->get('sortBy', session('lessons_sort_by', 'title'));
            $sortDesc = $request->boolean('sortDesc', session('lessons_sort_desc', false));

            // Сохраняем фильтры в сессии
            session(['lessons_search' => $search]);
            session(['lessons_subject_id' => $subjectId]);
            session(['lessons_sort_by' => $sortBy]);
            session(['lessons_sort_desc' => $sortDesc]);

            // Применяем фильтры
            if ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            }

            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }

            // Сортировка
            $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

            $lessons = $query->paginate(15);
            $subjects = Subject::all();

            \Log::info('Lessons index called', [
                'lessons_count' => $lessons->count(),
                'subjects_count' => $subjects->count(),
                'filters' => [
                    'search' => $search,
                    'subject_id' => $subjectId,
                    'sortBy' => $sortBy,
                    'sortDesc' => $sortDesc
                ]
            ]);

            return spa('Admin/Lessons/Index', [
                'lessons' => $lessons ?: (object)['data' => []],
                'subjects' => $subjects,
                'filters' => [
                    'search' => $search,
                    'subject_id' => $subjectId,
                    'sortBy' => $sortBy,
                    'sortDesc' => $sortDesc
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in lessons index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return spa('Admin/Lessons/Index', [
                'lessons' => (object)['data' => []],
                'subjects' => [],
                'filters' => [],
                'error' => 'Произошла ошибка при загрузке данных'
            ]);
        }
    }

    /**
     * Обработка фильтров через POST запрос
     */
    public function filter(Request $request)
    {
        try {
            $query = Lesson::with(['subject']);

            // Поиск по названию
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            }

            // Фильтр по предмету
            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->get('subject_id'));
            }

            $lessons = $query->orderBy('title')->paginate(15);
            $subjects = Subject::all();

            \Log::info('Lessons filter called', [
                'lessons_count' => $lessons->count(),
                'subjects_count' => $subjects->count(),
                'filters' => $request->only(['search', 'subject_id'])
            ]);

            return spa('Admin/Lessons/Index', [
                'lessons' => $lessons,
                'subjects' => $subjects,
                'filters' => $request->only(['search', 'subject_id']),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in lessons filter', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return spa('Admin/Lessons/Index', [
                'lessons' => (object)['data' => []],
                'subjects' => [],
                'filters' => $request->only(['search', 'subject_id']),
                'error' => 'Произошла ошибка при фильтрации данных'
            ]);
        }
    }

    /**
     * Очистка фильтров
     */
    public function clearFilters()
    {
        session()->forget(['lessons_search', 'lessons_subject_id', 'lessons_sort_by', 'lessons_sort_desc']);
        return redirect()->route('admin.lessons.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Получаем расписания с их предметами
        $schedules = Schedule::with(['subject', 'group', 'teacher'])->get();
        $totalLessons = Lesson::count();


        return spa('Admin/Lessons/Create', [
            'schedules' => $schedules,
            'totalLessons' => $totalLessons,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'schedule_id' => 'required|exists:schedules,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,txt,jpg,jpeg,png,gif,bmp,webp,md,html,css,js,json,xml,csv,log|max:10240'
        ]);

        try {
            // Получаем расписание для получения subject_id
            $schedule = \App\Models\Schedule::findOrFail($request->schedule_id);
            
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'subject_id' => $schedule->subject_id, // Берем subject_id из расписания
            ];

            // Если загружен файл
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('lessons', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getMimeType();
                $data['file_size'] = $file->getSize();
            }

            // Создаем урок
            $lesson = Lesson::create($data);

            // Привязываем урок к расписанию
            $lesson->schedules()->attach($request->schedule_id, [
                'order' => $schedule->lessons()->count() + 1,
                'duration' => null,
                'start_time' => null,
                'end_time' => null,
                'room' => null,
                'notes' => null
            ]);

            return redirect()->route('admin.lessons.index')
                ->with('success', 'Урок создан успешно и добавлен в расписание!');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при создании урока: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        $lesson->load(['subject', 'schedules.teacher', 'schedules.group']);
        return spa('Admin/Lessons/Show', [
            'lesson' => $lesson,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        // Загружаем урок с его расписаниями
        $lesson->load(['schedules.subject', 'schedules.group', 'schedules.teacher']);
        
        // Получаем все расписания с их предметами
        $schedules = \App\Models\Schedule::with(['subject', 'group', 'teacher'])->get();

        return spa('Admin/Lessons/Edit', [
            'lesson' => $lesson,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'schedule_id' => 'required|exists:schedules,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,txt,jpg,jpeg,png,gif,bmp,webp,md,html,css,js,json,xml,csv,log|max:10240'
        ]);

        try {
            // Получаем предмет из расписания
            $schedule = \App\Models\Schedule::findOrFail($request->schedule_id);
            
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'subject_id' => $schedule->subject_id,
            ];

            // Если загружен новый файл
            if ($request->hasFile('file')) {
                // Удаляем старый файл
                if ($lesson->file_path && Storage::disk('public')->exists($lesson->file_path)) {
                    Storage::disk('public')->delete($lesson->file_path);
                }

                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('lessons', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getMimeType();
                $data['file_size'] = $file->getSize();
            }

            $lesson->update($data);

            // Обновляем связь с расписанием
            $lesson->schedules()->sync([$schedule->id => [
                'order' => $schedule->lessons()->count() + 1,
                'duration' => null,
                'start_time' => null,
                'end_time' => null,
                'room' => null,
                'notes' => null
            ]]);

            return redirect()->route('admin.lessons.index')
                ->with('success', 'Урок обновлен успешно!');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при обновлении урока: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show materials for the specified lesson.
     */
    public function materials(Lesson $lesson)
    {
        $lesson->load(['subject', 'schedules.teacher', 'schedules.group']);
        return spa('Admin/Lessons/Materials', [
            'lesson' => $lesson,
        ]);
    }

    /**
     * Download lesson file.
     */
    public function download(Lesson $lesson)
    {
        if (!$lesson->file_path || !Storage::disk('public')->exists($lesson->file_path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('public')->download($lesson->file_path, $lesson->file_name);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        try {
            // Удаляем файл если он существует
            if ($lesson->file_path && Storage::disk('public')->exists($lesson->file_path)) {
                Storage::disk('public')->delete($lesson->file_path);
            }

            $lesson->delete();

            // Возвращаем успешный ответ без редиректа
            return response()->json([
                'success' => true,
                'message' => 'Урок удален успешно!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении урока: ' . $e->getMessage()
            ], 500);
        }
    }
}
