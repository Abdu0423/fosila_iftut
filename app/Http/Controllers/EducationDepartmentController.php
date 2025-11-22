<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Subject;

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
}

