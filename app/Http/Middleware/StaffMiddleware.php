<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect('/login');
        }

        if ($user->isStudent()) {
            if ($request->expectsJson() || $request->header('X-Spa') === '1') {
                return response()->json(['message' => 'Доступ запрещен.'], 403);
            }

            return spa('Errors/403', [
                'message' => 'Доступ запрещен.',
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}
