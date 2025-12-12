<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     * Простая система: URL параметр > Cookie > Сессия > Default
     */
    public function handle(Request $request, Closure $next)
    {
        // Приоритет получения языка:
        // 1. URL параметр (?lang=ru или ?lang=tg) - самый высокий приоритет
        // 2. Cookie (для сохранения выбора пользователя)
        // 3. Сессия
        // 4. Язык по умолчанию из конфига
        
        $requestLocale = $request->get('lang');
        $cookieLocale = $request->cookie('locale');
        $sessionLocale = Session::get('locale');
        $configLocale = config('app.locale', 'ru');
        
        $locale = null;
        
        // Проверяем URL параметр (самый высокий приоритет)
        if ($requestLocale && in_array($requestLocale, ['ru', 'tg'])) {
            $locale = $requestLocale;
        }
        // Проверяем cookie
        elseif ($cookieLocale && in_array($cookieLocale, ['ru', 'tg'])) {
            $locale = $cookieLocale;
        }
        // Используем сессию
        elseif ($sessionLocale && in_array($sessionLocale, ['ru', 'tg'])) {
            $locale = $sessionLocale;
        }
        // Используем язык по умолчанию
        else {
            $locale = $configLocale;
        }
        
        // Проверяем, что язык поддерживается
        if (!in_array($locale, ['ru', 'tg'])) {
            $locale = 'ru';
        }
        
        // Сохраняем в сессию и cookie для последующих запросов
        if (Session::get('locale') !== $locale) {
            Session::put('locale', $locale);
            Session::save();
        }
        
        // Устанавливаем локаль
        App::setLocale($locale);
        
        return $next($request);
    }
}
