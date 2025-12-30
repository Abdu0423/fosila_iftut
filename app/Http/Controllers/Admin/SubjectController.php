<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Subject::with('department');
        
        // Поиск по названию или описанию
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Фильтр по отделению
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Фильтр по активности
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $subjects = $query->paginate(20)
            ->withQueryString();

        // Загружаем отделения для фильтра
        $departments = \App\Models\Department::all();

        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => $subjects,
            'departments' => $departments,
            'filters' => [
                'search' => $request->search ?? '',
                'department_id' => $request->department_id ?? '',
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
        $departments = Department::all();
        
        // \Log::info('Subjects Create - Departments loaded', ['count' => $departments->count()]);

        return Inertia::render('Admin/Subjects/Create', [
            'departments' => $departments->toArray()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'content' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Предмет успешно создан!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subject $subject)
    {
        $subject->load('department', 'lessons', 'schedules');

        return Inertia::render('Admin/Subjects/Show', [
            'subject' => $subject
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject)
    {
        $departments = Department::all();
        $subject->load('department');
        
        // \Log::info('Subjects Edit - Loading subject', [
        //     'subject_id' => $subject->id,
        //     'subject_name' => $subject->name,
        //     'departments_count' => $departments->count()
        // ]);

        return Inertia::render('Admin/Subjects/Edit', [
            'subject' => $subject->toArray(),
            'departments' => $departments->toArray()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'content' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Предмет успешно обновлен!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        \Log::info('Attempting to delete subject', ['subject_id' => $subject->id, 'subject_name' => $subject->name]);
        
        // Проверяем, есть ли связанные уроки или расписания
        $lessonsCount = $subject->lessons()->count();
        $schedulesCount = $subject->schedules()->count();
        
        \Log::info('Subject relations check', [
            'lessons_count' => $lessonsCount,
            'schedules_count' => $schedulesCount
        ]);
        
        if ($lessonsCount > 0 || $schedulesCount > 0) {
            \Log::warning('Cannot delete subject - has relations', [
                'lessons' => $lessonsCount,
                'schedules' => $schedulesCount
            ]);
            return back()->with('error', 'Невозможно удалить предмет, так как у него есть связанные уроки или расписания.');
        }

        $subject->delete();
        \Log::info('Subject deleted successfully', ['subject_id' => $subject->id]);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Предмет успешно удален!');
    }

    /**
     * Toggle subject status (active/inactive)
     */
    public function toggleStatus(Subject $subject)
    {
        $subject->update([
            'is_active' => !$subject->is_active
        ]);

        $status = $subject->is_active ? 'активирован' : 'деактивирован';
        
        return back()->with('success', "Предмет успешно {$status}!");
    }

    /**
     * Bulk actions for subjects
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);

        $subjects = Subject::whereIn('id', $validated['subject_ids']);
        $count = $subjects->count();

        switch ($validated['action']) {
            case 'activate':
                $subjects->update(['is_active' => true]);
                return back()->with('success', "Активировано предметов: {$count}");
                
            case 'deactivate':
                $subjects->update(['is_active' => false]);
                return back()->with('success', "Деактивировано предметов: {$count}");
                
            case 'delete':
                // Проверяем, что у предметов нет связанных данных
                $subjectsWithRelations = $subjects->whereHas('lessons')
                    ->orWhereHas('schedules')
                    ->count();
                
                if ($subjectsWithRelations > 0) {
                    return back()->with('error', 'Некоторые предметы имеют связанные уроки или расписания и не могут быть удалены.');
                }
                
                $subjects->delete();
                return back()->with('success', "Удалено предметов: {$count}");
        }
    }

    /**
     * Duplicate a subject
     */
    public function duplicate(Subject $subject)
    {
        $newSubject = $subject->replicate();
        $newSubject->name = $subject->name . ' (копия)';
        $newSubject->is_active = false; // Копии создаются неактивными
        $newSubject->save();

        return redirect()->route('admin.subjects.edit', $newSubject)
            ->with('success', 'Предмет успешно скопирован! Отредактируйте данные.');
    }

    /**
     * Get subjects for API (used in lesson/schedule creation)
     */
    public function apiIndex(Request $request)
    {
        $subjects = Subject::query()
            ->when($request->active_only, function ($query) {
                $query->where('is_active', true);
            })
            ->when($request->department_id, function ($query, $departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->select('id', 'name', 'department_id')
            ->with('department:id,name')
            ->orderBy('name')
            ->get();

        return response()->json($subjects);
    }

    /**
     * Export subjects to CSV
     */
    public function export()
    {
        $subjects = Subject::with('department')->get();
        
        $filename = 'subjects_' . date('Y-m-d_H-i-s') . '.csv';
        $csvContent = '';
        $csvContent .= "\xEF\xBB\xBF"; // BOM для кириллицы
        
        $headers = ['ID', 'Название', 'Отделение', 'Статус', 'Дата создания'];
        $csvContent .= implode(';', $headers) . "\n";
        
        foreach ($subjects as $subject) {
            $row = [
                $subject->id,
                $subject->name,
                $subject->department->name ?? 'Не указано',
                $subject->is_active ? 'Активный' : 'Неактивный',
                $subject->created_at->format('Y-m-d H:i:s')
            ];
            $csvContent .= implode(';', $row) . "\n";
        }
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}