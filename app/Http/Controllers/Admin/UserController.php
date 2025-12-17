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
    public function index()
    {
        $users = User::with('role')
            ->orderBy('created_at', 'desc')
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
            'stats' => $stats
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

            // Генерируем email, если не указан
            if (empty($validated['email'])) {
                $validated['email'] = strtolower($validated['name']) . '.' . strtolower($validated['last_name']) . '@fosila.local';
            }
            
            // Генерируем случайный пароль, если не указан
            if (empty($validated['password'])) {
                $validated['password'] = \Str::random(12);
            }

            $user = User::create([
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
    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'dad_phone' => 'nullable|string|max:255',
                'mom_phone' => 'nullable|string|max:255',
                'role_id' => 'required|exists:roles,id',
                'group_id' => 'nullable|integer',
                'password' => 'nullable|string|min:8|confirmed'
            ]);

            // Проверяем, что хотя бы email или phone указан
            if (empty($validated['email']) && empty($validated['phone'])) {
                return back()->withErrors([
                    'email' => 'Необходимо указать хотя бы email или телефон',
                    'phone' => 'Необходимо указать хотя бы email или телефон'
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

            return redirect()->route('admin.users.index')
                ->with('success', 'Пользователь успешно обновлен');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Проверяем, есть ли ошибка дубликата email
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
            'education_department' => 'Отдел образования'
        ];

        return $displayNames[$roleName] ?? $roleName;
    }
}
