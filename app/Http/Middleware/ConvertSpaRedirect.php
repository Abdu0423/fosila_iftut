<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertSpaRedirect
{
    /**
     * Редиректы Laravel для SPA становятся JSON, чтобы Vue Router сам сменил страницу.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->isSpaRequest($request) || ! $response->isRedirection()) {
            return $response;
        }

        $location = $response->headers->get('Location') ?? '/';
        $path = $this->toSpaPath($location, $request);

        return response()->json([
            'redirect' => $path,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
        ]);
    }

    private function isSpaRequest(Request $request): bool
    {
        return $request->header('X-Spa') === '1'
            || $request->is('api')
            || $request->is('api/*')
            || $request->expectsJson();
    }

    private function toSpaPath(string $location, Request $request): string
    {
        $parts = parse_url($location) ?: [];
        $host = $parts['host'] ?? null;

        if ($host && $host !== $request->getHost()) {
            return $location;
        }

        $path = $parts['path'] ?? '/';
        if (str_starts_with($path, '/api/')) {
            $path = substr($path, 4) ?: '/';
        } elseif ($path === '/api') {
            $path = '/';
        }

        if (! empty($parts['query'])) {
            $path .= '?'.$parts['query'];
        }

        return $path;
    }
}
