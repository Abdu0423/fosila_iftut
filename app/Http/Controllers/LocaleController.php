<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    /**
     * Сменить язык
     */
    public function change(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:ru,tg'
        ]);
        
        $locale = $request->locale;
        
        // Также проверяем заголовок X-Locale (может быть отправлен из localStorage)
        $headerLocale = $request->header('X-Locale');
        if ($headerLocale && in_array($headerLocale, ['ru', 'tg'])) {
            $locale = $headerLocale;
        }
        
        \Log::info('Locale change requested', [
            'locale' => $locale,
            'header_locale' => $headerLocale,
            'body_locale' => $request->locale,
            'user_id' => $request->user()?->id
        ]);
        
        // Сохраняем в сессии (для синхронизации с сервером)
        Session::put('locale', $locale);
        Session::save();
        
        // Устанавливаем текущий язык
        App::setLocale($locale);
        
        // Возвращаем JSON ответ с cookie для установки на клиенте
        $response = response()->json([
            'success' => true,
            'locale' => $locale,
            'message' => 'Locale changed successfully'
        ]);
        
        // Устанавливаем cookie в ответе (для следующей загрузки страницы)
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

