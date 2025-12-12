<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    /**
     * Сменить язык
     * Простая реализация: сохраняем в сессию и cookie, возвращаем редирект
     */
    public function change(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:ru,tg'
        ]);
        
        $locale = $request->locale;
        
        // Сохраняем в сессии
        Session::put('locale', $locale);
        Session::save();
        
        // Устанавливаем текущий язык
        App::setLocale($locale);
        
        // Возвращаем редирект на текущую страницу с параметром языка
        // Это гарантирует, что язык будет применен сразу
        $redirectUrl = $request->header('Referer') ?: '/';
        
        // Убираем старые параметры lang из URL
        $parsedUrl = parse_url($redirectUrl);
        $path = $parsedUrl['path'] ?? '/';
        $query = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
            unset($query['lang']); // Убираем старый параметр
        }
        $query['lang'] = $locale; // Добавляем новый
        $newUrl = $path . '?' . http_build_query($query);
        
        // Устанавливаем cookie в ответе
        $response = redirect($newUrl);
        $response->cookie('locale', $locale, 60 * 24 * 365, '/', null, false, false); // 1 год
        
        return $response;
    }
    
    /**
     * Получить текущий язык
     */
    public function getCurrent()
    {
        return response()->json([
            'locale' => App::getLocale(),
            'available_locales' => ['ru', 'tg']
        ]);
    }
}

