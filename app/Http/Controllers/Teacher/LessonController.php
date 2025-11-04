<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Group;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        
        // Получаем расписания с подсчетом уроков для текущего учителя
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['subject', 'group'])
            ->withCount('lessons')
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
                    'lessons_count' => $schedule->lessons_count ?? 0,
                    'scheduled_at' => $schedule->scheduled_at ? $schedule->scheduled_at->format('d.m.Y H:i') : null,
                    'semester' => $schedule->semester,
                    'study_year' => $schedule->study_year,
                    'credits' => $schedule->credits,
                    'is_active' => $schedule->is_active,
                    'created_at' => $schedule->created_at->format('d.m.Y H:i'),
                ];
            });

        return Inertia::render('Teacher/Lessons/Index', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Показать уроки конкретного расписания
     */
    public function showSchedule(Schedule $schedule)
    {
        $teacher = Auth::user();
        
        // Проверяем, что расписание принадлежит текущему учителю
        if ($schedule->teacher_id !== $teacher->id) {
            abort(403, 'У вас нет доступа к этому расписанию');
        }

        // Загружаем уроки расписания с данными
        $schedule->load([
            'subject',
            'group',
            'lessons.subject'
        ]);

        // Получаем все доступные уроки для добавления (не принадлежащие этому расписанию)
        $availableLessons = Lesson::with(['subject'])
            ->whereNotIn('id', $schedule->lessons->pluck('id'))
            ->orderBy('title')
            ->get()
            ->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'description' => $lesson->description,
                    'subject' => $lesson->subject ? [
                        'id' => $lesson->subject->id,
                        'name' => $lesson->subject->name,
                    ] : null,
                    'file_name' => $lesson->file_name,
                    'file_type' => $lesson->file_type,
                    'file_size' => $lesson->file_size,
                    'file_size_formatted' => $lesson->file_size_formatted ?? 'Не указано',
                    'file_path' => $lesson->file_path,
                    'created_at' => $lesson->created_at ? $lesson->created_at->format('d.m.Y H:i') : null,
                    'updated_at' => $lesson->updated_at ? $lesson->updated_at->format('d.m.Y H:i') : null,
                ];
            });

        // Форматируем уроки для отображения (сортируем по order)
        $lessons = $schedule->lessons->sortBy(function ($lesson) {
            return $lesson->pivot->order ?? 0;
        })->map(function ($lesson) {
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
                    'duration' => $lesson->pivot->duration ?? null,
                    'start_time' => $lesson->pivot->start_time ?? null,
                    'end_time' => $lesson->pivot->end_time ?? null,
                    'room' => $lesson->pivot->room ?? null,
                    'notes' => $lesson->pivot->notes ?? null,
                ],
                'file_name' => $lesson->file_name,
                'file_size' => $lesson->file_size,
                'file_path' => $lesson->file_path,
                'created_at' => $lesson->created_at->format('d.m.Y H:i'),
                'updated_at' => $lesson->updated_at->format('d.m.Y H:i'),
            ];
        })->values();

        return Inertia::render('Teacher/Lessons/ShowSchedule', [
            'schedule' => [
                'id' => $schedule->id,
                'subject' => $schedule->subject ? [
                    'id' => $schedule->subject->id,
                    'name' => $schedule->subject->name,
                ] : null,
                'group' => $schedule->group ? [
                    'id' => $schedule->group->id,
                    'name' => $schedule->group->name,
                ] : null,
                'scheduled_at' => $schedule->scheduled_at ? $schedule->scheduled_at->format('d.m.Y H:i') : null,
                'semester' => $schedule->semester,
                'study_year' => $schedule->study_year,
                'credits' => $schedule->credits,
                'is_active' => $schedule->is_active,
            ],
            'lessons' => $lessons,
            'availableLessons' => $availableLessons,
        ]);
    }



    /**
     * Показать форму создания урока
     */
    public function create(Request $request)
    {
        $teacher = Auth::user();
        
        // Получаем расписания учителя для выбора
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['subject', 'group'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Получаем schedule_id из query параметров, если есть
        $selectedScheduleId = $request->query('schedule_id');

        return Inertia::render('Teacher/Lessons/Create', [
            'schedules' => $schedules,
            'selectedScheduleId' => $selectedScheduleId,
        ]);
    }

    /**
     * Создать новый урок
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Начало создания урока', $request->all());
            
            // Логируем информацию о файле
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                \Log::info('Информация о файле', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'extension' => $file->getClientOriginalExtension(),
                    'path' => $file->getPathname()
                ]);
            }
            
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'schedule_id' => 'required|exists:schedules,id',
                'file' => 'nullable|file|max:10240', // 10MB max - временно убираем ограничения по типу
            ]);

            $teacher = Auth::user();
            \Log::info('Учитель найден', ['teacher_id' => $teacher->id]);
            
            // Проверяем, что расписание принадлежит учителю
            $schedule = Schedule::where('id', $request->schedule_id)
                ->where('teacher_id', $teacher->id)
                ->firstOrFail();
            
            \Log::info('Расписание найдено', ['schedule_id' => $schedule->id, 'subject_id' => $schedule->subject_id]);

            $lessonData = [
                'title' => $request->title,
                'description' => $request->description,
                'subject_id' => $schedule->subject_id, // Берем предмет из расписания
            ];

            // Обработка загрузки файла
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('lessons', $fileName, 'public');
                
                $lessonData['file_name'] = $file->getClientOriginalName();
                $lessonData['file_size'] = $file->getSize();
                $lessonData['file_path'] = $filePath;
            }

            \Log::info('Данные урока для создания', $lessonData);
            $lesson = Lesson::create($lessonData);
            \Log::info('Урок создан', ['lesson_id' => $lesson->id]);

            // Добавляем урок к расписанию
            $schedule->lessons()->attach($lesson->id);
            \Log::info('Урок привязан к расписанию');

            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Урок успешно создан!',
                    'lesson' => $lesson
                ]);
            }
            
            return redirect()->route('teacher.lessons.index')
                ->with('success', 'Урок успешно создан!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Ошибка валидации', $e->errors());
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании урока', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при создании урока: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Ошибка при создании урока: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Показать урок
     */
    public function show(Lesson $lesson)
    {
        $teacher = Auth::user();
        
        // Проверяем, что урок принадлежит расписанию учителя
        $schedule = $lesson->schedules()
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$schedule) {
            abort(403, 'У вас нет доступа к этому уроку');
        }

        $lesson->load(['subject', 'schedules.group', 'schedules.subject']);

        return Inertia::render('Teacher/Lessons/Show', [
            'lesson' => $lesson,
            'schedule' => $schedule,
        ]);
    }

    /**
     * Показать форму редактирования урока
     */
    public function edit(Lesson $lesson)
    {
        $teacher = Auth::user();
        
        // Проверяем, что урок принадлежит расписанию учителя
        $schedule = $lesson->schedules()
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$schedule) {
            abort(403, 'У вас нет доступа к этому уроку');
        }

        // Получаем расписания учителя для выбора
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->with(['subject', 'group'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Teacher/Lessons/Edit', [
            'lesson' => $lesson,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Обновить урок
     */
    public function update(Request $request, Lesson $lesson)
    {
        $teacher = Auth::user();
        
        // Проверяем, что урок принадлежит расписанию учителя
        $schedule = $lesson->schedules()
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$schedule) {
            abort(403, 'У вас нет доступа к этому уроку');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'schedule_id' => 'required|exists:schedules,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,txt|max:10240', // 10MB max
        ]);

        // Получаем новое расписание для обновления subject_id
        $newSchedule = Schedule::where('id', $request->schedule_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'subject_id' => $newSchedule->subject_id, // Берем предмет из нового расписания
        ];

        // Обработка загрузки файла
        if ($request->hasFile('file')) {
            // Удаляем старый файл
            if ($lesson->file_path && \Storage::disk('public')->exists($lesson->file_path)) {
                \Storage::disk('public')->delete($lesson->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('lessons', $fileName, 'public');
            
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_size'] = $file->getSize();
            $updateData['file_path'] = $filePath;
        }

        $lesson->update($updateData);

        // Обновляем связь с расписанием если изменилось
        if ($request->schedule_id != $schedule->id) {
            $lesson->schedules()->detach($schedule->id);
            $lesson->schedules()->attach($newSchedule->id);
        }

        return redirect()->route('teacher.lessons.index')
            ->with('success', 'Урок успешно обновлен!');
    }

    /**
     * Удалить урок
     */
    public function destroy(Lesson $lesson)
    {
        $teacher = Auth::user();
        
        // Проверяем, что урок принадлежит расписанию учителя
        $schedule = $lesson->schedules()
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$schedule) {
            abort(403, 'У вас нет доступа к этому уроку');
        }

        // Удаляем файл если он существует
        if ($lesson->file_path && \Storage::disk('public')->exists($lesson->file_path)) {
            \Storage::disk('public')->delete($lesson->file_path);
        }

        $lesson->delete();

        return redirect()->route('teacher.lessons.index')
            ->with('success', 'Урок успешно удален!');
    }


}
