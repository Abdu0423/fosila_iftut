<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Показать список всех пользователей
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        // Поиск по ФИО, email, телефону
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Фильтр по роли
        if ($request->filled('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Фильтр по статусу
        if ($request->filled('status')) {
            if ($request->status === 'Подтвержден') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?? 'Не указано',
                    'last_name' => $user->last_name ?? 'Не указано',
                    'email' => $user->email ?? 'Не указано',
                    'role' => $user->role ? $user->role->name : 'Не назначена',
                    'role_id' => $user->role_id,
                    'created_at' => $user->created_at ? $user->created_at->format('d.m.Y H:i') : 'Не указано',
                    'updated_at' => $user->updated_at ? $user->updated_at->format('d.m.Y H:i') : 'Не указано',
                    'status' => $user->email_verified_at ? 'Подтвержден' : 'Не подтвержден',
                    'avatar' => $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'User')
                ];
            });

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $this->getRoleDisplayName($role->name)
            ];
        });

        $stats = [
            'total' => User::count(),
            'admins' => User::whereHas('role', function ($query) {
                $query->where('name', 'admin');
            })->count(),
            'teachers' => User::whereHas('role', function ($query) {
                $query->where('name', 'teacher');
            })->count(),
            'students' => User::whereHas('role', function ($query) {
                $query->where('name', 'student');
            })->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count()
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'stats' => $stats,
            'filters' => $request->only(['search', 'role', 'status'])
        ]);
    }

    /**
     * Показать информацию о пользователе
     */
    public function show(User $user)
    {
        $userData = [
            'id' => $user->id,
            'name' => $user->name ?? 'Не указано',
            'last_name' => $user->last_name ?? 'Не указано',
            'middle_name' => $user->middle_name ?? 'Не указано',
            'email' => $user->email ?? 'Не указано',
            'phone' => $user->phone ?? 'Не указано',
            'address' => $user->address ?? 'Не указано',
            'dad_phone' => $user->dad_phone ?? 'Не указано',
            'mom_phone' => $user->mom_phone ?? 'Не указано',
            'role' => $user->role ? $user->role->name : 'Не назначена',
            'role_display' => $user->role ? $this->getRoleDisplayName($user->role->name) : 'Не назначена',
            'role_id' => $user->role_id,
            'group_id' => $user->group_id,
            'created_at' => $user->created_at ? $user->created_at->format('d.m.Y H:i') : 'Не указано',
            'updated_at' => $user->updated_at ? $user->updated_at->format('d.m.Y H:i') : 'Не указано',
            'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->format('d.m.Y H:i') : null,
            'status' => $user->email_verified_at ? 'Подтвержден' : 'Не подтвержден',
            'avatar' => $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'User')
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => $userData
        ]);
    }

    /**
     * Показать форму создания пользователя
     */
    public function create()
    {
        $roles = Role::all()->map(function ($role) {
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

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
            'groups' => $groups
        ]);
    }

    /**
     * Сохранить нового пользователя
     */
    public function store(Request $request)
    {
        \Log::info('Начало создания пользователя', ['request_data' => $request->except(['password', 'password_confirmation'])]);
        
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

            // Проверяем, что хотя бы email или phone указан
            if (empty($validated['email']) && empty($validated['phone'])) {
                return back()->withErrors([
                    'email' => 'Необходимо указать хотя бы email или телефон',
                    'phone' => 'Необходимо указать хотя бы email или телефон'
                ])->withInput();
            }

            \Log::info('Валидация прошла успешно', ['validated_data' => array_keys($validated)]);

            // Генерируем случайный пароль, если не указан
            if (empty($validated['password'])) {
                $validated['password'] = \Str::random(12);
            }

            // Нормализуем телефоны
            $normalizedPhone = $this->normalizePhone($validated['phone'] ?? null);
            $normalizedDadPhone = $this->normalizePhone($validated['dad_phone'] ?? null);
            $normalizedMomPhone = $this->normalizePhone($validated['mom_phone'] ?? null);

            $user = User::create([
                'name' => $validated['name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'password' => bcrypt($validated['password']),
                'role_id' => $validated['role_id'],
                'group_id' => $validated['group_id'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $normalizedPhone,
                'dad_phone' => $normalizedDadPhone,
                'mom_phone' => $normalizedMomPhone,
                'email_verified_at' => now(), // Автоматически подтверждаем email для админ-созданных пользователей
                'must_change_password' => true // Требуем смену пароля при первом входе
            ]);

            \Log::info('Пользователь успешно создан', ['user_id' => $user->id, 'email' => $user->email]);

            return Inertia::location(route('admin.users.index'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Ошибка валидации при создании пользователя', ['errors' => $e->errors()]);
            
            // Проверяем, есть ли ошибка дубликата email
            if (isset($e->errors()['email']) && str_contains($e->errors()['email'][0], 'already been taken')) {
                return back()->withErrors([
                    'email' => 'Пользователь с таким email уже существует в системе'
                ])->withInput();
            }
            
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании пользователя', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', __('controllers.error_creating', ['message' => $e->getMessage()]))->withInput();
        }
    }

    /**
     * Показать форму редактирования пользователя
     */
    public function edit(User $user)
    {
        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $this->getRoleDisplayName($role->name)
            ];
        });

        // Получаем активные группы, но также включаем группу пользователя, если она неактивна
        $groupsQuery = Group::where(function($query) use ($user) {
            $query->where('status', 'active');
            if ($user->group_id) {
                $query->orWhere('id', $user->group_id);
            }
        });
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

        return Inertia::render('Admin/Users/Edit', [
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
                'group_id' => $user->group_id ? (int)$user->group_id : null,
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
    public function update(Request $request, User $user)
    {
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
                'group_id' => 'nullable|integer',
                'password' => 'nullable|string|min:4|confirmed'
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

            // Нормализуем телефоны
            $normalizedPhone = $this->normalizePhone($validated['phone'] ?? null);
            $normalizedDadPhone = $this->normalizePhone($validated['dad_phone'] ?? null);
            $normalizedMomPhone = $this->normalizePhone($validated['mom_phone'] ?? null);

            // Проверяем, что хотя бы email или phone указан (после нормализации)
            if (empty($validated['email']) && empty($normalizedPhone)) {
                return back()->withErrors([
                    'email' => 'Необходимо указать хотя бы email или телефон',
                    'phone' => 'Необходимо указать хотя бы email или телефон'
                ])->withInput();
            }

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

            if ($request->filled('password')) {
                $user->update([
                    'password' => bcrypt($request->password)
                ]);
            }

            // Возвращаем back() чтобы onSuccess на фронтенде обработал редирект
            return back()->with('success', 'Пользователь успешно обновлен');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Проверяем, есть ли ошибка дубликата email
            if (isset($e->errors()['email']) && str_contains($e->errors()['email'][0], 'already been taken')) {
                return back()->withErrors([
                    'email' => 'Пользователь с таким email уже существует в системе'
                ])->withInput();
            }
            
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Ошибка при обновлении пользователя', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Ошибка при обновлении пользователя: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Удалить пользователя
     */
    public function destroy(User $user)
    {
        \Log::info('Попытка удаления пользователя', ['user_id' => $user->id, 'auth_user_id' => auth()->id()]);
        
        // Не позволяем удалять самого себя
        if ($user->id === auth()->id()) {
            \Log::warning('Попытка удаления собственного аккаунта', ['user_id' => $user->id]);
            return back()->with('error', 'Нельзя удалить свой собственный аккаунт');
        }

        try {
            $user->delete();
            \Log::info('Пользователь успешно удален', ['user_id' => $user->id]);
            return back()->with('success', 'Пользователь успешно удален');
        } catch (\Exception $e) {
            \Log::error('Ошибка при удалении пользователя', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Ошибка при удалении пользователя: ' . $e->getMessage());
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
            'education_department' => 'Отдел образования',
            'registration_center' => 'Регистрационный центр'
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
}
