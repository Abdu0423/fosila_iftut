<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class SetLocale
{
    /**
     * Handle an incoming request.
     * Определяет язык из cookie, fallback на 'ru'
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->cookie('locale', 'ru');
        
        // Проверяем, что язык поддерживается
        if (!in_array($locale, ['ru', 'tg'])) {
            $locale = 'ru';
        }
        
        // Устанавливаем локаль
        App::setLocale($locale);
        
        return $next($request);
    }
}

