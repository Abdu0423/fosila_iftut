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
        
        // НЕ сохраняем в БД - используем только localStorage на клиенте
        // Это позволяет быстрее переключать язык без запросов к БД
        
        // Устанавливаем текущий язык
        App::setLocale($locale);
        
        // Возвращаем JSON ответ
        return response()->json([
            'success' => true,
            'locale' => $locale,
            'message' => 'Locale changed successfully'
        ]);
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

