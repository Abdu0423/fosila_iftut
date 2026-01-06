<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        Log::info('Показана страница входа');
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        Log::info('Получен запрос на вход', [
            'login' => $request->login,
            'has_password' => !empty($request->password)
        ]);

        // Валидация
        $validated = $request->validate([
            'login' => 'required|string',
            'password' => 'required',
        ]);

        $login = $validated['login'];
        $password = $validated['password'];

        // Нормализуем номер телефона (убираем все нецифровые символы кроме +)
        $normalizedPhone = preg_replace('/[^0-9+]/', '', $login);

        Log::info('Попытка входа', [
            'login' => $login,
            'normalized_phone' => $normalizedPhone
        ]);

        // Ищем пользователя по телефону с загрузкой роли
        // Нормализуем телефон для поиска (убираем все кроме цифр)
        $phoneDigits = preg_replace('/[^0-9]/', '', $login);
        
        // Ищем по телефону (точное совпадение или нормализованное)
        $user = \App\Models\User::with('role')->where(function ($query) use ($login, $normalizedPhone, $phoneDigits) {
            $query->where('phone', $login)
                ->orWhere('phone', $normalizedPhone)
                ->orWhere('phone', $phoneDigits);
            
            // Если номер достаточно длинный (больше 7 цифр), ищем по последним цифрам
            if (strlen($phoneDigits) >= 7) {
                $lastDigits = substr($phoneDigits, -7);
                $query->orWhere('phone', 'like', '%' . $lastDigits);
            }
        })->first();

        if (!$user) {
            Log::warning('Пользователь не найден', ['login' => $login]);
            return Inertia::back()->withErrors([
                'login' => 'Предоставленные учетные данные не соответствуют нашим записям.',
            ])->withInput(['login' => $login]);
        }

        // Проверяем пароль
        if (!\Hash::check($password, $user->password)) {
            Log::warning('Неверный пароль', ['user_id' => $user->id]);
            return Inertia::back()->withErrors([
                'login' => 'Предоставленные учетные данные не соответствуют нашим записям.',
            ])->withInput(['login' => $login]);
        }

        // Авторизуем пользователя
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        
        // Обновляем время последнего входа
        $user->update(['last_login_at' => now()]);
        
        Log::info('Пользователь успешно авторизован', [
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'role_id' => $user->role_id,
            'role' => $user->role ? $user->role->name : 'no role',
            'must_change_password' => $user->must_change_password
        ]);
        
        // ВАЖНО: Проверяем, нужно ли пользователю сменить пароль
        if ($user->must_change_password) {
            Log::info('Пользователь должен сменить пароль, перенаправление на /change-password', [
                'user_id' => $user->id
            ]);
            return Inertia::location(route('change-password'));
        }
        
        Log::info('Проверка роли пользователя', [
            'role_id' => $user->role_id,
            'role_name' => $user->role ? $user->role->name : 'no role'
        ]);
        
        // Проверяем роль пользователя через методы модели
        if ($user->isAdmin()) {
            Log::info('Перенаправление администратора на /admin');
            return Inertia::location('/admin');
        }

        if ($user->isTeacher()) {
            Log::info('Перенаправление преподавателя на /teacher/');
            return Inertia::location('/teacher/');
        }

        if ($user->isEducationDepartment()) {
            Log::info('Перенаправление отдела образования на /education');
            return Inertia::location('/education');
        }

        if ($user->isRegistrationCenter()) {
            Log::info('Перенаправление регистрационного центра на /registration');
            return Inertia::location('/registration');
        }

        if ($user->isStudent()) {
            Log::info('Перенаправление студента на /student/');
            return Inertia::location('/student/');
        }

        // Если роль не определена, перенаправляем на общий dashboard
        Log::warning('Неопределенная роль пользователя, перенаправление на /dashboard', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'role_name' => $user->role ? $user->role->name : 'no role'
        ]);
        return Inertia::location('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
