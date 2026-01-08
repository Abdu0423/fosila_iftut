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
use App\Models\Specialty;

class EducationDepartmentController extends Controller
{
    /**
     * Проверяет, что пользователь имеет роль отдела образования или регистрационного центра
     */
    protected function checkRole($user)
    {
        if (!$user->isEducationDepartment() && !$user->isRegistrationCenter()) {
            abort(403, 'Доступ запрещен');
        }
    }

    /**
     * Дашборд отдела образования
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Проверяем роль
        $this->checkRole($user);
        
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
        
        $this->checkRole($user);
        
        $query = User::with('role', 'group');
        
        // Ограничиваем только студентами
        $query->whereHas('role', function($q) {
            $q->where('name', 'student');
        });
        
        // Поиск по ФИО, email, телефону
        if ($request->has('search')) {
            $search = $request->search;
            // Заменяем пробелы на % для поиска по полному ФИО
            $searchTerm = str_replace(' ', '%', trim($search));
            
            $query->where(function($q) use ($search, $searchTerm) {
                // Поиск по отдельным полям
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
                
                // Поиск по полному ФИО (с учетом пробелов как %)
                if ($searchTerm !== $search) {
                    $q->orWhereRaw("CONCAT(COALESCE(name, ''), ' ', COALESCE(last_name, ''), ' ', COALESCE(middle_name, '')) LIKE ?", ["%{$searchTerm}%"]);
                }
            });
        }
        
        // Фильтр по группе
        if ($request->has('group') && $request->group) {
            $query->where('group_id', $request->group);
        }
        
        $users = $query->paginate(20);
        
        // Получаем список групп для фильтра
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
        
        return Inertia::render('EducationDepartment/Users/Index', [
            'users' => $users,
            'groups' => $groups,
            'filters' => $request->only(['search', 'group']),
        ]);
    }
    
    /**
     * Показать форму создания пользователя
     */
    public function createUser()
    {
        $user = auth()->user();
        
        $this->checkRole($user);

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
            'groups' => $groups
        ]);
    }
    
    /**
     * Сохранить нового пользователя
     */
    public function storeUser(Request $request)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'group_id' => 'nullable|integer|exists:groups,id',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'dad_phone' => 'nullable|string|max:255',
                'mom_phone' => 'nullable|string|max:255'
            ]);

            // Получаем роль студента
            $studentRole = Role::where('name', 'student')->first();
            if (!$studentRole) {
                return back()->withErrors([
                    'role' => 'Роль студента не найдена в системе'
                ])->withInput();
            }

            // Генерируем случайный пароль автоматически
            $password = \Str::random(12);

            // Нормализуем телефоны
            $normalizedPhone = $this->normalizePhone($validated['phone'] ?? null);
            $normalizedDadPhone = $this->normalizePhone($validated['dad_phone'] ?? null);
            $normalizedMomPhone = $this->normalizePhone($validated['mom_phone'] ?? null);

            User::create([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'password' => bcrypt($password),
                'role_id' => $studentRole->id,
                'group_id' => $validated['group_id'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $normalizedPhone,
                'dad_phone' => $normalizedDadPhone,
                'mom_phone' => $normalizedMomPhone,
                'must_change_password' => true
            ]);

            return redirect()->route('education.users.index')
                ->with('success', __('controllers.user_created'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (isset($e->errors()['email']) && str_contains($e->errors()['email'][0], 'already been taken')) {
                return back()->withErrors([
                    'email' => __('controllers.email_exists')
                ])->withInput();
            }
            
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', __('controllers.error_creating', ['message' => $e->getMessage()]))->withInput();
        }
    }
    
    /**
     * Показать форму редактирования пользователя
     */
    public function editUser(User $user)
    {
        $authUser = auth()->user();
        
        $this->checkRole($authUser);
        
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

        // Получаем активные группы, но также включаем группу пользователя, если она неактивна
        $groupsQuery = Group::where('status', 'active');
        if ($user->group_id) {
            $groupsQuery->orWhere('id', $user->group_id);
        }
        $groups = $groupsQuery
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
        
        $this->checkRole($authUser);
        
        // Проверяем, что редактируемый пользователь - teacher или student
        if (!$user->role || !in_array($user->role->name, ['teacher', 'student'])) {
            abort(403, 'Можно редактировать только преподавателей и студентов');
        }
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255',
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'dad_phone' => 'nullable|string|max:255',
                'mom_phone' => 'nullable|string|max:255',
                'role_id' => 'required|exists:roles,id',
                'group_id' => 'nullable|integer'
            ]);

            // Проверяем уникальность email, если он указан
            if (!empty($validated['email'])) {
                $existingUser = \App\Models\User::where('email', $validated['email'])
                    ->where('id', '!=', $user->id)
                    ->first();
                if ($existingUser) {
                    return back()->withErrors([
                        'email' => 'Email уже используется другим пользователем'
                    ])->withInput();
                }
            }

            // Проверяем, что хотя бы email или phone указан
            if (empty($validated['email']) && empty($validated['phone'])) {
                return back()->withErrors([
                    'email' => 'Необходимо указать хотя бы email или телефон',
                    'phone' => 'Необходимо указать хотя бы email или телефон'
                ])->withInput();
            }

            // Проверяем, что роль - teacher или student
            $role = Role::find($validated['role_id']);
            if (!$role || !in_array($role->name, ['teacher', 'student'])) {
                return back()->withErrors([
                    'role_id' => 'Можно назначать только роли преподавателя или студента'
                ])->withInput();
            }

            // Нормализуем телефоны
            $normalizedPhone = $this->normalizePhone($validated['phone'] ?? null);
            $normalizedDadPhone = $this->normalizePhone($validated['dad_phone'] ?? null);
            $normalizedMomPhone = $this->normalizePhone($validated['mom_phone'] ?? null);

            $user->update([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'email' => !empty($validated['email']) ? $validated['email'] : null,
                'phone' => $normalizedPhone,
                'address' => $validated['address'] ?? null,
                'dad_phone' => $normalizedDadPhone,
                'mom_phone' => $normalizedMomPhone,
                'role_id' => $validated['role_id'],
                'group_id' => $validated['group_id'] ?? null
            ]);

            // Возвращаем back() чтобы onSuccess на фронтенде обработал редирект
            return back()->with('success', __('controllers.user_updated'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (isset($e->errors()['email']) && str_contains($e->errors()['email'][0], 'already been taken')) {
                return back()->withErrors([
                    'email' => __('controllers.email_exists')
                ])->withInput();
            }
            
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Ошибка при обновлении пользователя', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', __('controllers.error_updating', ['message' => $e->getMessage()]))->withInput();
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
     * Нормализовать номер телефона
     * Добавляет +992 в начало, если его нет
     */
    private function normalizePhone($phone)
    {
        if (empty($phone) || $phone === '+992' || $phone === '992') {
            return null;
        }

        // Удаляем все символы кроме цифр и +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Если пусто после очистки или только +992, возвращаем null
        if (empty($phone) || $phone === '+992' || $phone === '992') {
            return null;
        }

        // Если номер уже начинается с +992, возвращаем как есть
        if (str_starts_with($phone, '+992')) {
            // Проверяем, что после +992 идет 9 цифр
            $digits = substr($phone, 4);
            if (preg_match('/^\d{9}$/', $digits)) {
                return $phone;
            }
            // Если после +992 нет 9 цифр, возвращаем null
            return null;
        }

        // Если номер начинается с 992 (без +), добавляем +
        if (str_starts_with($phone, '992')) {
            $digits = substr($phone, 3);
            if (preg_match('/^\d{9}$/', $digits)) {
                return '+' . $phone;
            }
            // Если после 992 нет 9 цифр, возвращаем null
            return null;
        }

        // Если номер начинается с 0, заменяем на +992
        if (str_starts_with($phone, '0')) {
            $digits = substr($phone, 1);
            if (preg_match('/^\d{9}$/', $digits)) {
                return '+992' . $digits;
            }
            return null;
        }

        // Если номер начинается с 9 и имеет 9 цифр, добавляем +992
        if (str_starts_with($phone, '9') && preg_match('/^\d{9}$/', $phone)) {
            return '+992' . $phone;
        }

        // Если номер состоит только из цифр (9 цифр), добавляем +992
        if (preg_match('/^\d{9}$/', $phone)) {
            return '+992' . $phone;
        }

        // Если номер не соответствует формату, но содержит цифры, пытаемся извлечь 9 последних цифр
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) >= 9) {
            $last9 = substr($digits, -9);
            return '+992' . $last9;
        }

        // Если ничего не подошло, возвращаем null (не валидный номер)
        return null;
    }
    
    /**
     * Страница групп
     */
    public function groups(Request $request)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $query = Group::query();
        
        // Поиск
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('enrollment_year', 'like', "%{$search}%")
                  ->orWhere('graduation_year', 'like', "%{$search}%");
            });
        }
        
        // Фильтр по статусу
        if ($request->filled('status')) {
            $status = $request->status;
            // Если выбран "inactive", показываем все неактивные статусы (graduated и suspended)
            if ($status === 'inactive') {
                $query->whereIn('status', ['graduated', 'suspended']);
            } else {
                $query->where('status', $status);
            }
        }
        
        $groups = $query->withCount('users')->orderBy('name')->paginate(20);
        
        return Inertia::render('EducationDepartment/Groups/Index', [
            'groups' => $groups,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
    
    /**
     * Показать форму создания группы
     */
    public function createGroup()
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $departments = Department::all()->map(function ($dept) {
            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'display_name' => $dept->name
            ];
        });
        
        $specialties = Specialty::all()->map(function ($spec) {
            return [
                'id' => $spec->id,
                'name' => $spec->name,
                'display_name' => $spec->name
            ];
        });

        return Inertia::render('EducationDepartment/Groups/Create', [
            'departments' => $departments,
            'specialties' => $specialties
        ]);
    }
    
    /**
     * Сохранить новую группу
     */
    public function storeGroup(Request $request)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'enrollment_year' => 'required|integer|min:2020|max:2030',
            'graduation_year' => 'required|integer|min:2020|max:2030|gte:enrollment_year',
            'status' => 'required|in:active,graduated,suspended',
            'department_id' => 'nullable|exists:departments,id',
            'course' => 'nullable|integer|min:1|max:6',
            'specialty_id' => 'nullable|exists:specialties,id'
        ]);
        
        Group::create($validated);
        
        return redirect()->route('education.groups.index')
            ->with('success', __('controllers.group_created'));
    }
    
    /**
     * Показать форму редактирования группы
     */
    public function editGroup(Group $group)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $departments = Department::all()->map(function ($dept) {
            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'display_name' => $dept->name
            ];
        });
        
        $specialties = Specialty::all()->map(function ($spec) {
            return [
                'id' => $spec->id,
                'name' => $spec->name,
                'display_name' => $spec->name
            ];
        });

        return Inertia::render('EducationDepartment/Groups/Edit', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'enrollment_year' => $group->enrollment_year,
                'graduation_year' => $group->graduation_year,
                'status' => $group->status,
                'department_id' => $group->department_id,
                'course' => $group->course,
                'specialty_id' => $group->specialty_id,
                'full_name' => $group->full_name
            ],
            'departments' => $departments,
            'specialties' => $specialties
        ]);
    }
    
    /**
     * Обновить группу
     */
    public function updateGroup(Request $request, Group $group)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'enrollment_year' => 'required|integer|min:2020|max:2030',
            'graduation_year' => 'required|integer|min:2020|max:2030|gte:enrollment_year',
            'status' => 'required|in:active,graduated,suspended',
            'department_id' => 'nullable|exists:departments,id',
            'course' => 'nullable|integer|min:1|max:6',
            'specialty_id' => 'nullable|exists:specialties,id'
        ]);
        
        $group->update($validated);
        
        return redirect()->route('education.groups.index')
            ->with('success', __('controllers.group_updated'));
    }
    
    /**
     * Страница расписаний
     */
    public function schedules(Request $request)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        // Загружаем все расписания без фильтрации (фильтрация на клиенте)
        $schedules = Schedule::with(['group', 'subject', 'teacher'])
            ->orderBy('scheduled_at', 'desc')
            ->get();
        
        return Inertia::render('EducationDepartment/Schedules/Index', [
            'schedules' => $schedules,
        ]);
    }
    
    /**
     * Страница предметов
     */
    public function subjects(Request $request)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $query = Subject::with('department');
        
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
        
        $this->checkRole($user);
        
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
        
        $this->checkRole($user);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
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
        
        $this->checkRole($user);
        
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
        
        $this->checkRole($user);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
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
        
        $this->checkRole($user);
        
        $subjects = Subject::where('is_active', true)->get();
        $groups = Group::all()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'full_name' => $group->full_name
            ];
        });
        $teachers = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->get()->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'last_name' => $teacher->last_name,
                'middle_name' => $teacher->middle_name,
                'email' => $teacher->email
            ];
        });
        
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
        
        $this->checkRole($user);
        
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
            'semester' => 'required|integer|min:1|max:10',
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
        
        $this->checkRole($user);
        
        $schedule->load(['subject', 'teacher', 'group']);
        $subjects = Subject::where('is_active', true)->get();
        $groups = Group::all()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'full_name' => $group->full_name
            ];
        });
        $teachers = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })->get()->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'last_name' => $teacher->last_name,
                'middle_name' => $teacher->middle_name,
                'email' => $teacher->email
            ];
        });
        
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
        
        $this->checkRole($user);
        
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
            'semester' => 'required|integer|min:1|max:10',
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
    
    /**
     * Страница кафедр
     */
    public function departments(Request $request)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $query = Department::query();
        
        // Поиск
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Фильтр по статусу
        if ($request->has('status') && $request->status !== null) {
            $query->where('is_active', $request->status);
        }
        
        $departments = $query->withCount('groups')
            ->orderBy('name')
            ->paginate(15);
        
        return Inertia::render('EducationDepartment/Departments/Index', [
            'departments' => $departments,
        ]);
    }
    
    /**
     * Показать форму создания кафедры
     */
    public function createDepartment()
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        return Inertia::render('EducationDepartment/Departments/Create');
    }
    
    /**
     * Сохранить новую кафедру
     */
    public function storeDepartment(Request $request)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        Department::create($validated);
        
        return redirect()->route('education.departments.index')
            ->with('success', __('messages.created_successfully', ['resource' => __('education_department.departments_title')]));
    }
    
    /**
     * Показать форму редактирования кафедры
     */
    public function editDepartment(Department $department)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        return Inertia::render('EducationDepartment/Departments/Edit', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'is_active' => $department->is_active,
            ]
        ]);
    }
    
    /**
     * Обновить кафедру
     */
    public function updateDepartment(Request $request, Department $department)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        $department->update($validated);
        
        return redirect()->route('education.departments.index')
            ->with('success', __('messages.updated_successfully', ['resource' => __('education_department.departments_title')]));
    }
    
    /**
     * Страница специальностей
     */
    public function specialties(Request $request)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $query = Specialty::query();
        
        // Поиск
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('short_name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Фильтр по статусу
        if ($request->has('status') && $request->status !== null) {
            $query->where('is_active', $request->status);
        }
        
        $specialties = $query->with('department:id,name')
            ->withCount('groups')
            ->orderBy('name')
            ->paginate(15);
        
        return Inertia::render('EducationDepartment/Specialties/Index', [
            'specialties' => $specialties,
        ]);
    }
    
    /**
     * Показать форму создания специальности
     */
    public function createSpecialty()
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return Inertia::render('EducationDepartment/Specialties/Create', [
            'departments' => $departments
        ]);
    }
    
    /**
     * Сохранить новую специальность
     */
    public function storeSpecialty(Request $request)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:50|unique:specialties,code',
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:1|max:10',
            'is_active' => 'boolean',
            'department_id' => 'nullable|exists:departments,id'
        ]);
        
        Specialty::create($validated);
        
        return redirect()->route('education.specialties.index')
            ->with('success', __('messages.created_successfully', ['resource' => __('education_department.specialties_title')]));
    }
    
    /**
     * Показать форму редактирования специальности
     */
    public function editSpecialty(Specialty $specialty)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return Inertia::render('EducationDepartment/Specialties/Edit', [
            'specialty' => [
                'id' => $specialty->id,
                'name' => $specialty->name,
                'short_name' => $specialty->short_name,
                'code' => $specialty->code,
                'description' => $specialty->description,
                'duration_years' => $specialty->duration_years,
                'is_active' => $specialty->is_active,
                'department_id' => $specialty->department_id,
            ],
            'departments' => $departments
        ]);
    }
    
    /**
     * Обновить специальность
     */
    public function updateSpecialty(Request $request, Specialty $specialty)
    {
        $user = auth()->user();
        
        $this->checkRole($user);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:50|unique:specialties,code,' . $specialty->id,
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:1|max:10',
            'is_active' => 'boolean'
        ]);
        
        $specialty->update($validated);
        
        return redirect()->route('education.specialties.index')
            ->with('success', __('messages.updated_successfully', ['resource' => __('education_department.specialties_title')]));
    }
}

