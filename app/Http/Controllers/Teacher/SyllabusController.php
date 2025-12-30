<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use App\Models\Subject;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SyllabusController extends Controller
{
    /**
     * Показать список силлабусов текущего учителя
     */
    public function index()
    {
        $teacherId = auth()->id();
        
        // Получаем все силлабусы, к которым у учителя есть доступ:
        // 1. Силлабусы, созданные учителем
        // 2. Силлабусы, привязанные к расписаниям учителя
        $syllabuses = Syllabus::where(function ($query) use ($teacherId) {
            $query->where('created_by', $teacherId)
                  ->orWhereHas('schedules', function ($q) use ($teacherId) {
                      $q->where('teacher_id', $teacherId);
                  });
        })
        ->with(['subject', 'creator'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($syllabus) {
            return [
                'id' => $syllabus->id,
                'name' => $syllabus->name,
                'description' => $syllabus->description,
                'file_name' => $syllabus->file_name,
                'file_type' => $syllabus->file_type,
                'file_size_formatted' => $syllabus->file_size_formatted,
                'subject_name' => $syllabus->subject ? $syllabus->subject->name : 'Не указан',
                'creation_year' => $syllabus->creation_year,
                'created_at' => $syllabus->created_at ? $syllabus->created_at->format('d.m.Y H:i') : 'Не указано',
                'download_url' => $syllabus->download_url,
                'preview_url' => $syllabus->preview_url,
            ];
        });
        
        // Получаем расписания учителя для статистики
        $schedules = Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'group'])
            ->get();

        $stats = [
            'total' => $syllabuses->count(),
            'this_year' => $syllabuses->where('creation_year', date('Y'))->count(),
            'last_year' => $syllabuses->where('creation_year', date('Y') - 1)->count(),
            'with_files' => $syllabuses->whereNotNull('file_name')->count(),
        ];

        return Inertia::render('Teacher/Syllabuses/Index', [
            'syllabuses' => $syllabuses->values(),
            'stats' => $stats,
            'schedules' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'subject_name' => $schedule->subject ? $schedule->subject->name : 'Без предмета',
                    'group_name' => $schedule->group ? $schedule->group->name : 'Без группы',
                    'start_date' => $schedule->start_date,
                    'end_date' => $schedule->end_date,
                ];
            })
        ]);
    }

    /**
     * Показать форму создания силлабуса
     */
    public function create()
    {
        $teacherId = auth()->id();
        
        // Получаем предметы из расписаний учителя (если есть)
        $schedules = Schedule::where('teacher_id', $teacherId)
            ->with('subject')
            ->get();
        
        $scheduleSubjects = $schedules->pluck('subject')
            ->filter()
            ->unique('id')
            ->pluck('id')
            ->toArray();
        
        // Получаем все активные предметы, но приоритет отдаем предметам из расписаний
        $allSubjects = Subject::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($subject) use ($scheduleSubjects) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'display_name' => $subject->name,
                    'from_schedule' => in_array($subject->id, $scheduleSubjects)
                ];
            })
            ->sortBy(function ($subject) {
                // Сначала предметы из расписаний, потом остальные
                return $subject['from_schedule'] ? 0 : 1;
            })
            ->values();

        $years = [];
        $currentYear = date('Y');
        for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
            $years[] = $i;
        }

        return Inertia::render('Teacher/Syllabuses/Create', [
            'subjects' => $allSubjects,
            'years' => $years
        ]);
    }

    /**
     * Сохранить новый силлабус
     */
    public function store(Request $request)
    {
        \Log::info('Начало создания силлабуса учителем', [
            'teacher_id' => auth()->id(),
            'request_data' => $request->except(['file']),
            'has_file' => $request->hasFile('file'),
        ]);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'creation_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,txt,jpg,jpeg,png,gif,bmp,webp,md,html,css,js,json,xml,csv,log|max:10240'
        ]);

        try {
            $file = $request->file('file');
            \Log::info('Файл получен', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);
            
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('syllabuses', $fileName, 'public');
            
            \Log::info('Файл сохранен', ['file_path' => $filePath]);

            $syllabus = Syllabus::create([
                'name' => $request->name,
                'description' => $request->description,
                'subject_id' => $request->subject_id,
                'creation_year' => $request->creation_year,
                'created_by' => auth()->id(),
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            \Log::info('Силлабус создан', ['syllabus_id' => $syllabus->id]);

            return Inertia::location(route('teacher.syllabuses.show', $syllabus->id));
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании силлабуса', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Ошибка при создании силлабуса: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Показать информацию о силлабусе
     */
    public function show(Syllabus $syllabus)
    {
        // Проверяем, что силлабус принадлежит учителю
        $teacherId = auth()->id();
        
        // Проверяем, что силлабус создан текущим учителем ИЛИ привязан к расписанию учителя
        $hasAccess = $syllabus->created_by == $teacherId || 
                     Schedule::where('teacher_id', $teacherId)
                        ->whereHas('syllabuses', function ($query) use ($syllabus) {
                            $query->where('syllabus_id', $syllabus->id);
                        })
                        ->exists();

        if (!$hasAccess) {
            abort(403, 'У вас нет доступа к этому силлабусу');
        }

        $syllabusData = [
            'id' => $syllabus->id,
            'name' => $syllabus->name,
            'description' => $syllabus->description,
            'file_name' => $syllabus->file_name,
            'file_type' => $syllabus->file_type,
            'file_size_formatted' => $syllabus->file_size_formatted,
            'subject_name' => $syllabus->subject ? $syllabus->subject->name : 'Не указан',
            'subject_id' => $syllabus->subject_id,
            'creation_year' => $syllabus->creation_year,
            'created_at' => $syllabus->created_at ? $syllabus->created_at->format('d.m.Y H:i') : 'Не указано',
            'updated_at' => $syllabus->updated_at ? $syllabus->updated_at->format('d.m.Y H:i') : 'Не указано',
            'download_url' => $syllabus->download_url,
            'preview_url' => $syllabus->preview_url,
        ];

        return Inertia::render('Teacher/Syllabuses/Show', [
            'syllabus' => $syllabusData
        ]);
    }

    /**
     * Показать форму редактирования силлабуса
     */
    public function edit(Syllabus $syllabus)
    {
        // Проверяем, что силлабус принадлежит учителю
        $teacherId = auth()->id();
        
        // Проверяем, что силлабус создан текущим учителем ИЛИ привязан к расписанию учителя
        $hasAccess = $syllabus->created_by == $teacherId || 
                     Schedule::where('teacher_id', $teacherId)
                        ->whereHas('syllabuses', function ($query) use ($syllabus) {
                            $query->where('syllabus_id', $syllabus->id);
                        })
                        ->exists();

        if (!$hasAccess) {
            abort(403, 'У вас нет доступа к этому силлабусу');
        }

        // Получаем предметы из расписаний учителя
        $schedules = Schedule::where('teacher_id', $teacherId)
            ->with('subject')
            ->get();
        
        $subjects = $schedules->pluck('subject')
            ->filter()
            ->unique('id')
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'display_name' => $subject->name
                ];
            })->sortBy('name');

        $years = [];
        $currentYear = date('Y');
        for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
            $years[] = $i;
        }

        return Inertia::render('Teacher/Syllabuses/Edit', [
            'syllabus' => [
                'id' => $syllabus->id,
                'name' => $syllabus->name,
                'description' => $syllabus->description,
                'subject_id' => $syllabus->subject_id,
                'creation_year' => $syllabus->creation_year,
                'file_name' => $syllabus->file_name,
                'file_type' => $syllabus->file_type,
                'file_size_formatted' => $syllabus->file_size_formatted,
            ],
            'subjects' => $subjects->values(),
            'years' => $years
        ]);
    }

    /**
     * Обновить силлабус
     */
    public function update(Request $request, Syllabus $syllabus)
    {
        // Проверяем, что силлабус принадлежит учителю
        $teacherId = auth()->id();
        
        // Проверяем, что силлабус создан текущим учителем ИЛИ привязан к расписанию учителя
        $hasAccess = $syllabus->created_by == $teacherId || 
                     Schedule::where('teacher_id', $teacherId)
                        ->whereHas('syllabuses', function ($query) use ($syllabus) {
                            $query->where('syllabus_id', $syllabus->id);
                        })
                        ->exists();

        if (!$hasAccess) {
            abort(403, 'У вас нет доступа к этому силлабусу');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'creation_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,txt,md,html,css,js,json,xml,csv,log|max:10240'
        ]);

        try {
            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'subject_id' => $request->subject_id,
                'creation_year' => $request->creation_year,
            ];

            // Если загружен новый файл
            if ($request->hasFile('file')) {
                // Удаляем старый файл
                if ($syllabus->file_path && Storage::disk('public')->exists($syllabus->file_path)) {
                    Storage::disk('public')->delete($syllabus->file_path);
                }

                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('syllabuses', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getMimeType();
                $data['file_size'] = $file->getSize();
            }

            $syllabus->update($data);

            return redirect()->route('teacher.syllabuses.show', $syllabus->id)
                ->with('success', 'Силлабус успешно обновлен');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при обновлении силлабуса: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Удалить силлабус
     */
    public function destroy(Syllabus $syllabus)
    {
        // Проверяем, что силлабус принадлежит учителю
        $teacherId = auth()->id();
        
        // Проверяем, что силлабус создан текущим учителем ИЛИ привязан к расписанию учителя
        $hasAccess = $syllabus->created_by == $teacherId || 
                     Schedule::where('teacher_id', $teacherId)
                        ->whereHas('syllabuses', function ($query) use ($syllabus) {
                            $query->where('syllabus_id', $syllabus->id);
                        })
                        ->exists();

        if (!$hasAccess) {
            abort(403, 'У вас нет доступа к этому силлабусу');
        }

        try {
            // Удаляем файл
            if ($syllabus->file_path && Storage::disk('public')->exists($syllabus->file_path)) {
                Storage::disk('public')->delete($syllabus->file_path);
            }

            $syllabus->delete();

            return redirect()->route('teacher.syllabuses.index')
                ->with('success', 'Силлабус успешно удален');
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при удалении силлабуса: ' . $e->getMessage());
        }
    }

    /**
     * Скачать файл силлабуса
     */
    public function download(Syllabus $syllabus)
    {
        // Проверяем, что силлабус принадлежит учителю
        $teacherId = auth()->id();
        
        // Проверяем, что силлабус создан текущим учителем ИЛИ привязан к расписанию учителя
        $hasAccess = $syllabus->created_by == $teacherId || 
                     Schedule::where('teacher_id', $teacherId)
                        ->whereHas('syllabuses', function ($query) use ($syllabus) {
                            $query->where('syllabus_id', $syllabus->id);
                        })
                        ->exists();

        if (!$hasAccess) {
            abort(403, 'У вас нет доступа к этому силлабусу');
        }

        if (!$syllabus->file_path || !Storage::disk('public')->exists($syllabus->file_path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::disk('public')->download($syllabus->file_path, $syllabus->file_name);
    }

    /**
     * Предварительный просмотр файла
     */
    public function preview(Syllabus $syllabus)
    {
        // Проверяем, что силлабус принадлежит учителю
        $teacherId = auth()->id();
        
        // Проверяем, что силлабус создан текущим учителем ИЛИ привязан к расписанию учителя
        $hasAccess = $syllabus->created_by == $teacherId || 
                     Schedule::where('teacher_id', $teacherId)
                        ->whereHas('syllabuses', function ($query) use ($syllabus) {
                            $query->where('syllabus_id', $syllabus->id);
                        })
                        ->exists();

        if (!$hasAccess) {
            abort(403, 'У вас нет доступа к этому силлабусу');
        }

        if (!$syllabus->file_path || !Storage::disk('public')->exists($syllabus->file_path)) {
            abort(404, 'Файл не найден');
        }

        // Для PDF файлов показываем в браузере
        if ($syllabus->file_type === 'application/pdf') {
            return response()->file(Storage::disk('public')->path($syllabus->file_path));
        }

        // Для других файлов предлагаем скачать
        return redirect()->route('teacher.syllabuses.download', $syllabus->id);
    }
}
