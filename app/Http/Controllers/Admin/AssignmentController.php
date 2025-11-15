<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\Schedule;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Assignment::with(['lesson', 'schedule', 'group', 'teacher']);
        
        // Поиск
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Фильтр по уроку
        if ($request->filled('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }

        // Фильтр по группе
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $assignments = $query->paginate(20)
            ->withQueryString();

        // Загружаем данные для фильтров
        $lessons = Lesson::all();
        $groups = Group::all();

        return Inertia::render('Admin/Assignments/Index', [
            'assignments' => $assignments,
            'lessons' => $lessons,
            'groups' => $groups,
            'filters' => $request->only(['search', 'lesson_id', 'group_id', 'status', 'sort_by', 'sort_order'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lessons = Lesson::all();
        $schedules = Schedule::with(['lesson', 'group'])->get();
        $groups = Group::all();
        $teachers = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->get();

        return Inertia::render('Admin/Assignments/Create', [
            'lessons' => $lessons,
            'schedules' => $schedules,
            'groups' => $groups,
            'teachers' => $teachers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lesson_id' => 'nullable|exists:lessons,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'group_id' => 'required|exists:groups,id',
            'teacher_id' => 'required|exists:users,id',
            'due_date' => 'required|date',
            'max_points' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published,closed',
            'file' => 'nullable|file|max:10240', // 10MB max
            'instructions' => 'nullable|string'
        ]);

        // Обработка файла
        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('assignments', 'public');
        }

        $assignment = Assignment::create($validated);

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Задание успешно создано');
    }

    /**
     * Display the specified resource.
     */
    public function show(Assignment $assignment)
    {
        $assignment->load(['lesson', 'schedule', 'group', 'teacher', 'submissions.student']);

        return Inertia::render('Admin/Assignments/Show', [
            'assignment' => $assignment
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Assignment $assignment)
    {
        $assignment->load(['lesson', 'schedule', 'group', 'teacher']);
        
        $lessons = Lesson::all();
        $schedules = Schedule::with(['lesson', 'group'])->get();
        $groups = Group::all();
        $teachers = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->get();

        return Inertia::render('Admin/Assignments/Edit', [
            'assignment' => $assignment,
            'lessons' => $lessons,
            'schedules' => $schedules,
            'groups' => $groups,
            'teachers' => $teachers
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lesson_id' => 'nullable|exists:lessons,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'group_id' => 'required|exists:groups,id',
            'teacher_id' => 'required|exists:users,id',
            'due_date' => 'required|date',
            'max_points' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published,closed',
            'file' => 'nullable|file|max:10240',
            'instructions' => 'nullable|string'
        ]);

        // Обработка файла
        if ($request->hasFile('file')) {
            // Удаляем старый файл
            if ($assignment->file_path) {
                Storage::disk('public')->delete($assignment->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('assignments', 'public');
        }

        $assignment->update($validated);

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Задание успешно обновлено');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Assignment $assignment)
    {
        // Удаляем файл, если есть
        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        $assignment->delete();

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Задание успешно удалено');
    }

    /**
     * Bulk action (delete, change status)
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,change_status',
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'exists:assignments,id',
            'status' => 'required_if:action,change_status|in:draft,published,closed'
        ]);

        $assignments = Assignment::whereIn('id', $validated['assignment_ids']);

        switch ($validated['action']) {
            case 'delete':
                foreach ($assignments->get() as $assignment) {
                    if ($assignment->file_path) {
                        Storage::disk('public')->delete($assignment->file_path);
                    }
                }
                $assignments->delete();
                return back()->with('success', 'Выбранные задания удалены');

            case 'change_status':
                $assignments->update(['status' => $validated['status']]);
                return back()->with('success', 'Статус заданий обновлен');
        }
    }
}

