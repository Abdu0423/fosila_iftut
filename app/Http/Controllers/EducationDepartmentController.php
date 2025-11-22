<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Group;
use App\Models\Department;

class EducationDepartmentController extends Controller
{
    /**
     * Дашборд отдела образования
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Проверяем роль
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Статистика
        $stats = [
            'total_users' => User::count(),
            'total_teachers' => User::whereHas('role', function($q) {
                $q->where('name', 'teacher');
            })->count(),
            'total_students' => User::whereHas('role', function($q) {
                $q->where('name', 'student');
            })->count(),
            'total_subjects' => Subject::count(),
            'total_schedules' => Schedule::count(),
        ];
        
        return Inertia::render('EducationDepartment/Dashboard', [
            'stats' => $stats,
        ]);
    }
    
    /**
     * Страница пользователей
     */
    public function users(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        $query = User::with('role', 'group');
        
        // Поиск
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Фильтр по роли
        if ($request->has('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        $users = $query->paginate(20);
        
        return Inertia::render('EducationDepartment/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }
    
    /**
     * Страница расписаний
     */
    public function schedules(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        $query = Schedule::with(['group', 'subject', 'teacher']);
        
        // Фильтр по дате (используем scheduled_at)
        if ($request->has('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }
        
        // Фильтр по группе
        if ($request->has('group_id')) {
            $query->where('group_id', $request->group_id);
        }
        
        $schedules = $query->orderBy('scheduled_at')->paginate(20);
        
        return Inertia::render('EducationDepartment/Schedules/Index', [
            'schedules' => $schedules,
            'filters' => $request->only(['date', 'group_id']),
        ]);
    }
    
    /**
     * Страница предметов
     */
    public function subjects(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        $query = Subject::query();
        
        // Поиск
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        $subjects = $query->paginate(20);
        
        return Inertia::render('EducationDepartment/Subjects/Index', [
            'subjects' => $subjects,
            'filters' => $request->only(['search']),
        ]);
    }
    
    /**
     * Показать форму создания предмета
     */
    public function createSubject()
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        $departments = Department::all();
        
        return Inertia::render('EducationDepartment/Subjects/Create', [
            'departments' => $departments,
        ]);
    }
    
    /**
     * Сохранить новый предмет
     */
    public function storeSubject(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:subjects,code',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
            'credits' => 'nullable|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);
        
        Subject::create($validated);
        
        return redirect()->route('education.subjects.index')
            ->with('success', __('messages.created_successfully', ['resource' => __('education_department.subjects_title')]));
    }
    
    /**
     * Показать форму редактирования предмета
     */
    public function editSubject(Subject $subject)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        $subject->load('department');
        $departments = Department::all();
        
        return Inertia::render('EducationDepartment/Subjects/Edit', [
            'subject' => $subject,
            'departments' => $departments,
        ]);
    }
    
    /**
     * Обновить предмет
     */
    public function updateSubject(Request $request, Subject $subject)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:subjects,code,' . $subject->id,
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
            'credits' => 'nullable|integer|min:1|max:10',
            'is_active' => 'boolean',
        ]);
        
        $subject->update($validated);
        
        return redirect()->route('education.subjects.index')
            ->with('success', __('messages.updated_successfully', ['resource' => __('education_department.subjects_title')]));
    }
    
    /**
     * Показать форму создания расписания
     */
    public function createSchedule()
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        $subjects = Subject::where('is_active', true)->get();
        $groups = Group::all();
        $teachers = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->get();
        
        return Inertia::render('EducationDepartment/Schedules/Create', [
            'subjects' => $subjects,
            'groups' => $groups,
            'teachers' => $teachers,
        ]);
    }
    
    /**
     * Сохранить новое расписание
     */
    public function storeSchedule(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
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
        ]);
        
        Schedule::create($validated);
        
        return redirect()->route('education.schedules.index')
            ->with('success', __('messages.created_successfully', ['resource' => __('education_department.schedules_title')]));
    }
    
    /**
     * Показать форму редактирования расписания
     */
    public function editSchedule(Schedule $schedule)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        $schedule->load(['subject', 'teacher', 'group']);
        $subjects = Subject::where('is_active', true)->get();
        $groups = Group::all();
        $teachers = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->get();
        
        return Inertia::render('EducationDepartment/Schedules/Edit', [
            'schedule' => $schedule,
            'subjects' => $subjects,
            'groups' => $groups,
            'teachers' => $teachers,
        ]);
    }
    
    /**
     * Обновить расписание
     */
    public function updateSchedule(Request $request, Schedule $schedule)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
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
        ]);
        
        $schedule->update($validated);
        
        return redirect()->route('education.schedules.index')
            ->with('success', __('messages.updated_successfully', ['resource' => __('education_department.schedules_title')]));
    }
}

