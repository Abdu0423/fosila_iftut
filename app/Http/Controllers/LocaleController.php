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
        
        \Log::info('Locale change requested', ['locale' => $locale, 'user_id' => $request->user()?->id]);
        
        // Сохраняем в сессии
        Session::put('locale', $locale);
        Session::save(); // Принудительно сохраняем сессию
        
        \Log::info('Locale saved to session', ['session_locale' => Session::get('locale')]);
        
        // Если пользователь авторизован, сохраняем в БД
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
            \Log::info('Locale saved to user', ['user_locale' => $request->user()->locale]);
        }
        
        // Устанавливаем текущий язык
        App::setLocale($locale);
        
        // Возвращаем JSON ответ вместо редиректа для fetch API
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

