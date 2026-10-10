<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServeSpaShell
{
    /**
     * Обычный переход в браузере получает HTML-оболочку Vue.
     * Запросы данных идут на /api и не перехватываются.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        if ($request->is('api') || $request->is('api/*') || $request->is('sanctum/*') || $request->is('broadcasting/*')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Spa') === '1') {
            return $next($request);
        }

        if ($this->isFileRequest($request)) {
            return $next($request);
        }

        return response()->view('app');
    }

    private function isFileRequest(Request $request): bool
    {
        $path = $request->path();

        if ($path === 'favicon.ico' || str_starts_with($path, 'storage/') || str_starts_with($path, 'build/')) {
            return true;
        }

        return str_contains($path, 'download') || str_contains($path, 'export');
    }
}
