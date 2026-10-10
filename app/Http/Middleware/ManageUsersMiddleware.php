<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManageUsersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if (!$user->isAdmin() && !$user->isEducationDepartment()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Доступ запрещен.'], 403);
            }

            return spa('Errors/403', [
                'message' => 'Доступ запрещен.',
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}
