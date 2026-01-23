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
        $isAjax = $request->expectsJson() || $request->ajax();
        
        try {
            Log::info('Получен запрос на вход', [
                'login' => $request->login,
                'has_password' => !empty($request->password),
                'is_ajax' => $isAjax
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
                
                if ($isAjax) {
                    return response()->json([
                        'message' => 'Неверный номер телефона или пароль.',
                        'errors' => ['login' => ['Неверный номер телефона или пароль.']]
                    ], 422);
                }
                
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'login' => ['Неверный номер телефона или пароль.'],
                ]);
            }

            // Проверяем пароль
            if (!\Hash::check($password, $user->password)) {
                Log::warning('Неверный пароль', ['user_id' => $user->id]);
                
                if ($isAjax) {
                    return response()->json([
                        'message' => 'Неверный номер телефона или пароль.',
                        'errors' => ['login' => ['Неверный номер телефона или пароль.']]
                    ], 422);
                }
                
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'login' => ['Неверный номер телефона или пароль.'],
                ]);
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
            
            // Определяем URL для редиректа
            $redirectUrl = '/';
            
            if ($user->must_change_password) {
                $redirectUrl = route('change-password');
            } elseif ($user->isAdmin()) {
                $redirectUrl = '/admin';
            } elseif ($user->isTeacher()) {
                $redirectUrl = '/teacher/';
            } elseif ($user->isEducationDepartment()) {
                $redirectUrl = '/education';
            } elseif ($user->isRegistrationCenter()) {
                $redirectUrl = '/registration';
            } elseif ($user->isStudent()) {
                $redirectUrl = '/student/';
            }
            
            Log::info('Перенаправление на ' . $redirectUrl);
            
            // Для AJAX возвращаем JSON с URL для редиректа
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'redirect' => $redirectUrl
                ]);
            }
            
            return Inertia::location($redirectUrl);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Ошибка валидации при входе', [
                'errors' => $e->errors()
            ]);
            
            if ($isAjax) {
                return response()->json([
                    'message' => 'Ошибка валидации',
                    'errors' => $e->errors()
                ], 422);
            }
            
            throw $e;
        } catch (\Exception $e) {
            Log::error('Ошибка при входе', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($isAjax) {
                return response()->json([
                    'message' => 'Произошла ошибка при входе.',
                    'errors' => ['login' => ['Произошла ошибка при входе. Пожалуйста, попробуйте снова.']]
                ], 500);
            }
            
            throw \Illuminate\Validation\ValidationException::withMessages([
                'login' => ['Произошла ошибка при входе. Пожалуйста, попробуйте снова.'],
            ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
