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
        // Проверяем язык из запроса (например, ?lang=tg или ?_locale=ru)
        $requestLocale = $request->get('lang') ?? $request->get('_locale');
        
        if ($requestLocale) {
            $locale = $requestLocale;
            
            // Проверяем, что язык поддерживается
            if (in_array($locale, ['ru', 'tg'])) {
                Session::put('locale', $locale);
                Session::save(); // Принудительно сохраняем сессию
                
                // Если пользователь авторизован, сохраняем его предпочтение
                if ($request->user()) {
                    $request->user()->update(['locale' => $locale]);
                }
            }
        }
        
        // Получаем язык в следующем порядке приоритета:
        // 1. Из запроса (URL параметр) - самый высокий приоритет
        // 2. Из профиля пользователя (если авторизован) - приоритет над сессией
        // 3. Из сессии
        // 4. Язык по умолчанию из конфига
        $sessionLocale = Session::get('locale');
        $userLocale = $request->user() ? $request->user()->locale : null;
        $configLocale = config('app.locale', 'ru');
        
        \Log::info('SetLocale middleware', [
            'request_locale' => $requestLocale,
            'session_locale' => $sessionLocale,
            'user_locale' => $userLocale,
            'config_locale' => $configLocale,
            'user_id' => $request->user()?->id
        ]);
        
        // Если есть locale в запросе, используем его (уже обработано выше)
        if ($requestLocale && in_array($requestLocale, ['ru', 'tg'])) {
            $locale = $requestLocale;
        } else {
            // Приоритет: пользователь > сессия > конфиг
            if ($request->user() && $userLocale) {
                $locale = $userLocale;
                // Синхронизируем сессию с locale пользователя, если они не совпадают
                if ($sessionLocale !== $userLocale) {
                    Session::put('locale', $userLocale);
                    Session::save();
                }
            } elseif ($sessionLocale) {
                $locale = $sessionLocale;
            } else {
                $locale = $configLocale;
            }
        }
        
        // Проверяем, что язык поддерживается
        if (!in_array($locale, ['ru', 'tg'])) {
            $locale = 'ru';
        }
        
        // Устанавливаем локаль
        App::setLocale($locale);
        
        // Проверяем, что локаль установлена правильно
        $actualLocale = App::getLocale();
        
        \Log::info('SetLocale: Setting locale', [
            'final_locale' => $locale,
            'app_locale_before' => App::getLocale(),
            'app_locale_after' => $actualLocale,
            'locale_set' => $actualLocale === $locale
        ]);
        
        // Если локаль не установилась, пробуем ещё раз
        if ($actualLocale !== $locale) {
            \Log::warning('Locale not set correctly, retrying', [
                'expected' => $locale,
                'actual' => $actualLocale
            ]);
            App::setLocale($locale);
        }
        
        return $next($request);
    }
}

