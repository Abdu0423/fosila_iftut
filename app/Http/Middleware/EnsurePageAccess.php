<?php

namespace App\Http\Middleware;

use App\Support\PageAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePageAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! PageAccess::allows($user, '/'.$request->path())) {
            if ($request->expectsJson() || $request->header('X-Spa') === '1' || $request->is('api/*')) {
                return response()->json(['message' => 'Доступ к странице закрыт.'], 403);
            }

            return spa('Errors/403', [
                'message' => 'Доступ к странице закрыт.',
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}
