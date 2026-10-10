<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            \Log::info('AdminMiddleware: Пользователь не аутентифицирован, перенаправление на /login');
            return redirect('/login');
        }

        $user = auth()->user();
        \Log::info('AdminMiddleware: Проверка доступа', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'url' => $request->url()
        ]);

        if ($user->role_id != 1) {
            \Log::warning('AdminMiddleware: Доступ запрещен', [
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'url' => $request->url()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Доступ запрещен. Требуются права администратора.'], 403);
            }
            
            return spa('Errors/403', [
                'message' => 'Доступ запрещен. Требуются права администратора.'
            ])->toResponse($request)->setStatusCode(403);
        }

        \Log::info('AdminMiddleware: Доступ разрешен', [
            'user_id' => $user->id,
            'role_id' => $user->role_id
        ]);

        return $next($request);
    }
}
