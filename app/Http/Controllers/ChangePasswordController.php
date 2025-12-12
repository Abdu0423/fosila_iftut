<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ChangePasswordController extends Controller
{
    /**
     * Показать страницу смены пароля
     */
    public function show()
    {
        return Inertia::render('Auth/ChangePassword', [
            'user' => auth()->user()->only(['id', 'name', 'email']),
        ]);
    }

    /**
     * Обновить пароль пользователя
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ], [
            'current_password.required' => __('auth.current_password_required'),
            'password.required' => __('auth.password_required'),
            'password.confirmed' => __('auth.password_confirmed'),
            'password.min' => __('auth.password_min', ['min' => 4]),
        ]);

        $user = auth()->user();

        // Проверяем текущий пароль
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => __('auth.current_password_incorrect')
            ]);
        }

        // Проверяем что новый пароль отличается от текущего
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => __('auth.password_must_differ')
            ]);
        }

        // Обновляем пароль и снимаем флаг обязательной смены
        // Используем явное присваивание для гарантии обновления
        $user->password = Hash::make($request->password);
        $user->must_change_password = 0;
        $user->save();

        // Перезагружаем пользователя с ролью для правильного определения роли
        $user->refresh();
        $user->load('role');
        
        // Проверяем, что обновление прошло успешно
        if ($user->must_change_password) {
            \Log::error('Ошибка: must_change_password не обновлен', [
                'user_id' => $user->id,
                'must_change_password' => $user->must_change_password
            ]);
        }

        // Определяем куда перенаправить в зависимости от роли
        $redirectUrl = $this->getRedirectUrl($user);

        // Используем обычный redirect() для гарантированного редиректа
        // Это работает как обычный HTTP редирект и гарантирует переход
        return redirect($redirectUrl)
            ->with('success', __('auth.password_changed'));
    }

    /**
     * Определить URL для перенаправления в зависимости от роли
     */
    protected function getRedirectUrl($user)
    {
        if ($user->isAdmin()) {
            return '/admin';
        } elseif ($user->isTeacher()) {
            return '/teacher/';
        } elseif ($user->isEducationDepartment()) {
            return '/education';
        } else {
            return '/student/';
        }
    }
}

