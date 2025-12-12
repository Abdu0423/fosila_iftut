<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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

        // Получаем свежую модель из базы данных (не кешированную)
        $userId = auth()->id();
        $user = User::find($userId);

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

        // Обновляем напрямую через DB для гарантии
        DB::table('users')
            ->where('id', $userId)
            ->update([
                'password' => Hash::make($request->password),
                'must_change_password' => 0,
                'updated_at' => now()
            ]);

        // Перезагружаем пользователя с ролью для правильного определения роли
        $user = User::with('role')->find($userId);

        // Определяем куда перенаправить в зависимости от роли
        $redirectUrl = $this->getRedirectUrl($user);

        // Используем обычный redirect() для гарантированного редиректа
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

