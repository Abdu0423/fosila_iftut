<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Если пользователь не авторизован, пропускаем
        if (!$user) {
            return $next($request);
        }

        // Если пользователь уже на странице смены пароля или выхода, пропускаем
        if ($request->routeIs('change-password') || 
            $request->routeIs('change-password.update') ||
            $request->routeIs('logout') ||
            $request->routeIs('admin.logout') ||
            $request->routeIs('teacher.logout') ||
            $request->routeIs('student.logout')) {
            return $next($request);
        }

        // Если пользователь должен сменить пароль
        if ($user->must_change_password) {
            return redirect()->route('change-password')
                ->with('warning', 'Пожалуйста, смените ваш пароль для продолжения работы.');
        }

        return $next($request);
    }
}
