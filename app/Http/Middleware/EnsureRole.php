<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $name = $user?->role?->name;

        if (! $user || ! in_array($name, $roles, true)) {
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
