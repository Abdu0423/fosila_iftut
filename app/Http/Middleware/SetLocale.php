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
     */
    public function handle(Request $request, Closure $next)
    {
        // Приоритет получения языка:
        // 1. Заголовок X-Locale (отправляется из localStorage на фронтенде) - самый высокий приоритет
        // 2. Cookie (для первой загрузки страницы)
        // 3. URL параметр (?lang=ru или ?_locale=tg)
        // 4. Сессия
        // 5. Язык по умолчанию из конфига
        
        $headerLocale = $request->header('X-Locale');
        $cookieLocale = $request->cookie('locale');
        $requestLocale = $request->get('lang') ?? $request->get('_locale');
        $sessionLocale = Session::get('locale');
        $configLocale = config('app.locale', 'ru');
        
        $locale = null;
        
        // Проверяем заголовок X-Locale (из localStorage) - самый высокий приоритет
        if ($headerLocale && in_array($headerLocale, ['ru', 'tg'])) {
            $locale = $headerLocale;
        }
        // Проверяем cookie (для первой загрузки страницы)
        elseif ($cookieLocale && in_array($cookieLocale, ['ru', 'tg'])) {
            $locale = $cookieLocale;
        }
        // Проверяем URL параметр
        elseif ($requestLocale && in_array($requestLocale, ['ru', 'tg'])) {
            $locale = $requestLocale;
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
        
        // Сохраняем в сессию для последующих запросов
        if (Session::get('locale') !== $locale) {
            Session::put('locale', $locale);
            Session::save();
        }
        
        // Устанавливаем локаль
        App::setLocale($locale);
        
        return $next($request);
    }
}

