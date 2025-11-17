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
        // Проверяем язык из запроса (например, ?lang=tg)
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            
            // Проверяем, что язык поддерживается
            if (in_array($locale, ['ru', 'tg'])) {
                Session::put('locale', $locale);
                
                // Если пользователь авторизован, сохраняем его предпочтение
                if ($request->user()) {
                    $request->user()->update(['locale' => $locale]);
                }
            }
        }
        
        // Получаем язык в следующем порядке приоритета:
        // 1. Из сессии
        // 2. Из профиля пользователя (если авторизован)
        // 3. Язык по умолчанию из конфига
        $locale = Session::get('locale');
        
        if (!$locale && $request->user()) {
            $locale = $request->user()->locale;
        }
        
        if (!$locale) {
            $locale = config('app.locale', 'tg');
        }
        
        // Проверяем, что язык поддерживается
        if (!in_array($locale, ['ru', 'tg'])) {
            $locale = 'tg';
        }
        
        App::setLocale($locale);
        
        return $next($request);
    }
}

