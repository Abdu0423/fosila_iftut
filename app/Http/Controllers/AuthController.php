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

        // Определяем, является ли ввод email или телефоном
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;
        
        // Нормализуем номер телефона (убираем все нецифровые символы кроме +)
        $normalizedPhone = $isEmail ? null : preg_replace('/[^0-9+]/', '', $login);

        Log::info('Попытка входа', [
            'login' => $login,
            'is_email' => $isEmail,
            'normalized_phone' => $normalizedPhone
        ]);

        // Ищем пользователя по email или телефону
        $user = null;
        if ($isEmail) {
            $user = \App\Models\User::where('email', $login)->first();
        } else {
            // Нормализуем телефон для поиска (убираем все кроме цифр)
            $phoneDigits = preg_replace('/[^0-9]/', '', $login);
            
            // Ищем по телефону (точное совпадение или нормализованное)
            $user = \App\Models\User::where(function ($query) use ($login, $normalizedPhone, $phoneDigits) {
                $query->where('phone', $login)
                    ->orWhere('phone', $normalizedPhone)
                    ->orWhere('phone', $phoneDigits);
                
                // Если номер достаточно длинный (больше 7 цифр), ищем по последним цифрам
                if (strlen($phoneDigits) >= 7) {
                    $lastDigits = substr($phoneDigits, -7);
                    $query->orWhere('phone', 'like', '%' . $lastDigits);
                }
            })->first();
        }

        if (!$user) {
            Log::warning('Пользователь не найден', ['login' => $login]);
            return back()->withErrors([
                'login' => 'Предоставленные учетные данные не соответствуют нашим записям.',
            ])->onlyInput('login');
        }

        // Проверяем пароль
        if (!\Hash::check($password, $user->password)) {
            Log::warning('Неверный пароль', ['user_id' => $user->id]);
            return back()->withErrors([
                'login' => 'Предоставленные учетные данные не соответствуют нашим записям.',
            ])->onlyInput('login');
        }

        // Авторизуем пользователя
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        
        Log::info('Пользователь успешно авторизован', [
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'role_id' => $user->role_id,
            'role' => $user->role ? $user->role->name : 'no role'
        ]);
        
        Log::info('Проверка роли пользователя', [
            'role_id' => $user->role_id,
            'role_name' => $user->role ? $user->role->name : 'no role'
        ]);
        
        // Проверяем по role_id: 1 = admin, 2 = teacher, 3 = student
        if ($user->role_id == 1) {
            Log::info('Перенаправление администратора на /admin');
            return redirect('/admin');
        }

        if ($user->role_id == 2) {
            Log::info('Перенаправление учителя на /teacher/');
            return redirect('/teacher/');
        }

        if ($user->role_id == 3) {
            Log::info('Перенаправление студента на /student/');
            return redirect('/student/');
        }

        // Если роль не определена, перенаправляем на общий dashboard
        Log::warning('Неопределенная роль пользователя, перенаправление на /dashboard', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'role_name' => $user->role ? $user->role->name : 'no role'
        ]);
        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
