<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Facades\Sms;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsController extends Controller
{
    /**
     * Показать страницу отправки SMS
     */
    public function index()
    {
        $users = User::with(['role', 'group'])
            ->orderBy('last_name')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?? 'Не указано',
                    'last_name' => $user->last_name ?? 'Не указано',
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'role' => $user->role ? $user->role->name : null,
                    'role_display' => $user->role ? $this->getRoleDisplayName($user->role->name) : 'Не назначена',
                    'group' => $user->group ? $user->group->name : null,
                    'group_id' => $user->group_id,
                    'credentials_sent' => $user->credentials_sent ?? false,
                ];
            });

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $this->getRoleDisplayName($role->name)
            ];
        });

        $groups = Group::orderBy('name')->get()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
            ];
        });

        return Inertia::render('Admin/Sms/Index', [
            'users' => $users,
            'roles' => $roles,
            'groups' => $groups,
        ]);
    }

    /**
     * Отправить логин и пароль пользователям
     */
    public function sendCredentials(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $users = User::whereIn('id', $validated['user_ids'])
            ->whereNotNull('phone')
            ->get();

        if ($users->isEmpty()) {
            return back()->withErrors([
                'users' => 'Не найдено пользователей с указанными телефонами'
            ]);
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                // Определяем логин (email или phone)
                $login = $user->email ?? $user->phone;
                
                // Генерируем новый временный пароль
                $tempPassword = Str::random(8);
                
                // Формируем сообщение с предупреждением о безопасности
                $message = "Логин ва пароли шумо барои ворид шудан ба iftut.tj:\nРаками тел: {$login}\nПарол: {$tempPassword}\nДар вакти бори аввал ворид шудан рамз бояд иваз карда шавад.\n\nДИҚҚАТ: Лутфан логин ва пароли худро ба ягон кас надиҳед!";

                $result = Sms::send($user->phone, $message);

                if ($result['success']) {
                    // Обновляем пароль и статус отправки только при успешной отправке SMS
                    $user->update([
                        'password' => bcrypt($tempPassword),
                        'must_change_password' => true, // Требуем смену пароля при первом входе
                        'credentials_sent' => true // Отметка об отправке
                    ]);
                    $successCount++;
                    Log::info('SMS с данными для входа отправлено', [
                        'user_id' => $user->id,
                        'phone' => $user->phone
                    ]);
                } else {
                    $errorCount++;
                    $errors[] = "Пользователь {$user->name} {$user->last_name}: {$result['message']}";
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Пользователь {$user->name} {$user->last_name}: {$e->getMessage()}";
                Log::error('Ошибка отправки SMS с данными для входа', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($successCount > 0) {
            $message = "Успешно отправлено {$successCount} SMS";
            if ($errorCount > 0) {
                $message .= ", ошибок: {$errorCount}";
            }
            return redirect()->route('admin.sms.index')->with('success', $message);
        } else {
            return redirect()->route('admin.sms.index')->withErrors([
                'sms' => 'Не удалось отправить SMS: ' . implode(', ', $errors)
            ]);
        }
    }

    /**
     * Отправить произвольное SMS пользователям
     */
    public function sendCustom(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'message' => 'required|string|min:1|max:1000',
        ]);

        $users = User::whereIn('id', $validated['user_ids'])
            ->whereNotNull('phone')
            ->get();

        if ($users->isEmpty()) {
            return back()->withErrors([
                'users' => 'Не найдено пользователей с указанными телефонами'
            ]);
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                $result = Sms::send($user->phone, $validated['message']);

                if ($result['success']) {
                    $successCount++;
                    Log::info('Произвольное SMS отправлено', [
                        'user_id' => $user->id,
                        'phone' => $user->phone
                    ]);
                } else {
                    $errorCount++;
                    $errors[] = "Пользователь {$user->name} {$user->last_name}: {$result['message']}";
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Пользователь {$user->name} {$user->last_name}: {$e->getMessage()}";
                Log::error('Ошибка отправки произвольного SMS', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($successCount > 0) {
            $message = "Успешно отправлено {$successCount} SMS";
            if ($errorCount > 0) {
                $message .= ", ошибок: {$errorCount}";
            }
            return redirect()->route('admin.sms.index')->with('success', $message);
        } else {
            return redirect()->route('admin.sms.index')->withErrors([
                'sms' => 'Не удалось отправить SMS: ' . implode(', ', $errors)
            ]);
        }
    }

    /**
     * Получить отображаемое имя роли
     */
    private function getRoleDisplayName($roleName)
    {
        $roleNames = [
            'admin' => 'Администратор',
            'teacher' => 'Преподаватель',
            'student' => 'Студент',
            'education_department' => 'Отдел образования',
            'registration_center' => 'Регистрационный центр',
        ];

        return $roleNames[$roleName] ?? $roleName;
    }
}

