<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (Auth::user()->role_id != 2) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Доступ запрещен. Требуются права учителя.'], 403);
            }
            
            return spa('Errors/403', [
                'message' => 'Доступ запрещен. Требуются права учителя.'
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}
