<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     * Определяет язык из cookie или сессии, fallback на 'ru'
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = null;
        
        // Приоритет: 1. URL параметр, 2. Сессия, 3. Cookie, 4. Пользователь, 5. По умолчанию
        
        // 1. Проверяем URL параметр
        if ($request->has('lang') && in_array($request->query('lang'), ['ru', 'tg'])) {
            $locale = $request->query('lang');
        }
        
        // 2. Проверяем сессию
        if (!$locale && Session::has('locale')) {
            $sessionLocale = Session::get('locale');
            if (in_array($sessionLocale, ['ru', 'tg'])) {
                $locale = $sessionLocale;
            }
        }
        
        // 3. Проверяем cookie
        if (!$locale) {
            $cookieLocale = $request->cookie('locale');
            if ($cookieLocale && in_array($cookieLocale, ['ru', 'tg'])) {
                $locale = $cookieLocale;
            }
        }
        
        // 4. Проверяем настройки пользователя
        if (!$locale && $request->user()) {
            $userLocale = $request->user()->locale;
            if ($userLocale && in_array($userLocale, ['ru', 'tg'])) {
                $locale = $userLocale;
            }
        }
        
        // 5. По умолчанию русский
        if (!$locale) {
            $locale = 'ru';
        }
        
        // Устанавливаем локаль
        App::setLocale($locale);
        
        // Сохраняем в сессию для последующих запросов
        Session::put('locale', $locale);
        
        return $next($request);
    }
}
