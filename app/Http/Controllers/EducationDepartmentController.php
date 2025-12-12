<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Role;
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
        
        // Ограничиваем только преподавателями и студентами
        $query->whereHas('role', function($q) {
            $q->whereIn('name', ['teacher', 'student']);
        });
        
        // Поиск
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Фильтр по роли (только teacher или student)
        if ($request->has('role') && in_array($request->role, ['teacher', 'student'])) {
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
     * Показать форму создания пользователя
     */
    public function createUser()
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Только роли преподавателя и студента
        $roles = Role::whereIn('name', ['teacher', 'student'])->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $this->getRoleDisplayName($role->name)
            ];
        });

        $groups = Group::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->full_name,
                    'display_name' => $group->full_name
                ];
            });

        return Inertia::render('EducationDepartment/Users/Create', [
            'roles' => $roles,
            'groups' => $groups
        ]);
    }
    
    /**
     * Сохранить нового пользователя
     */
    public function storeUser(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users',
                'password' => 'nullable|string|min:4|confirmed',
                'role_id' => 'required|exists:roles,id',
                'group_id' => 'nullable|integer',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'dad_phone' => 'nullable|string|max:255',
                'mom_phone' => 'nullable|string|max:255'
            ]);

            // Проверяем, что роль - teacher или student
            $role = Role::find($validated['role_id']);
            if (!$role || !in_array($role->name, ['teacher', 'student'])) {
                return back()->withErrors([
                    'role_id' => 'Можно создавать только преподавателей и студентов'
                ])->withInput();
            }

            // Генерируем email, если не указан
            if (empty($validated['email'])) {
                $validated['email'] = strtolower($validated['name']) . '.' . strtolower($validated['last_name']) . '@fosila.local';
            }
            
            // Генерируем случайный пароль, если не указан
            if (empty($validated['password'])) {
                $validated['password'] = \Str::random(12);
            }

            User::create([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role_id' => $validated['role_id'],
                'group_id' => $validated['group_id'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'dad_phone' => $validated['dad_phone'] ?? null,
                'mom_phone' => $validated['mom_phone'] ?? null,
                'email_verified_at' => now(),
                'must_change_password' => true
            ]);

            return redirect()->route('education.users.index')
                ->with('success', 'Пользователь успешно создан');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (isset($e->errors()['email']) && str_contains($e->errors()['email'][0], 'already been taken')) {
                return back()->withErrors([
                    'email' => 'Пользователь с таким email уже существует в системе'
                ])->withInput();
            }
            
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при создании пользователя: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Показать форму редактирования пользователя
     */
    public function editUser(User $user)
    {
        $authUser = auth()->user();
        
        if (!$authUser->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что редактируемый пользователь - teacher или student
        if (!$user->role || !in_array($user->role->name, ['teacher', 'student'])) {
            abort(403, 'Можно редактировать только преподавателей и студентов');
        }
        
        // Только роли преподавателя и студента
        $roles = Role::whereIn('name', ['teacher', 'student'])->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $this->getRoleDisplayName($role->name)
            ];
        });

        $groups = Group::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->full_name,
                    'display_name' => $group->full_name
                ];
            });

        return Inertia::render('EducationDepartment/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name ?? '',
                'last_name' => $user->last_name ?? '',
                'middle_name' => $user->middle_name ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'address' => $user->address ?? '',
                'dad_phone' => $user->dad_phone ?? '',
                'mom_phone' => $user->mom_phone ?? '',
                'role_id' => $user->role_id,
                'group_id' => $user->group_id,
                'created_at' => $user->created_at ? $user->created_at->format('d.m.Y H:i') : 'Не указано',
                'updated_at' => $user->updated_at ? $user->updated_at->format('d.m.Y H:i') : 'Не указано',
                'email_verified_at' => $user->email_verified_at
            ],
            'roles' => $roles,
            'groups' => $groups
        ]);
    }
    
    /**
     * Обновить пользователя
     */
    public function updateUser(Request $request, User $user)
    {
        $authUser = auth()->user();
        
        if (!$authUser->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Проверяем, что редактируемый пользователь - teacher или student
        if (!$user->role || !in_array($user->role->name, ['teacher', 'student'])) {
            abort(403, 'Можно редактировать только преподавателей и студентов');
        }
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'dad_phone' => 'nullable|string|max:255',
                'mom_phone' => 'nullable|string|max:255',
                'role_id' => 'required|exists:roles,id',
                'group_id' => 'nullable|integer',
                'password' => 'nullable|string|min:4|confirmed'
            ]);

            // Проверяем, что роль - teacher или student
            $role = Role::find($validated['role_id']);
            if (!$role || !in_array($role->name, ['teacher', 'student'])) {
                return back()->withErrors([
                    'role_id' => 'Можно назначать только роли преподавателя или студента'
                ])->withInput();
            }

            $user->update([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'dad_phone' => $validated['dad_phone'],
                'mom_phone' => $validated['mom_phone'],
                'role_id' => $validated['role_id'],
                'group_id' => $validated['group_id']
            ]);

            if ($request->filled('password')) {
                $user->update([
                    'password' => bcrypt($request->password)
                ]);
            }

            return redirect()->route('education.users.index')
                ->with('success', 'Пользователь успешно обновлен');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (isset($e->errors()['email']) && str_contains($e->errors()['email'][0], 'already been taken')) {
                return back()->withErrors([
                    'email' => 'Пользователь с таким email уже существует в системе'
                ])->withInput();
            }
            
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при обновлении пользователя: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Получить отображаемое имя роли
     */
    private function getRoleDisplayName($roleName)
    {
        $displayNames = [
            'admin' => 'Администратор',
            'teacher' => 'Учитель',
            'student' => 'Студент',
            'education_department' => 'Отдел образования'
        ];

        return $displayNames[$roleName] ?? $roleName;
    }
    
    /**
     * Страница групп
     */
    public function groups(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isEducationDepartment()) {
            abort(403, 'Доступ запрещен');
        }
        
        $query = Group::query();
        
        // Поиск
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
        }
        
        // Фильтр по статусу
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $groups = $query->withCount('users')->orderBy('name')->paginate(20);
        
        return Inertia::render('EducationDepartment/Groups/Index', [
            'groups' => $groups,
            'filters' => $request->only(['search', 'status']),
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

