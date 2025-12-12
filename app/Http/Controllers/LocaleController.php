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
        
        // Если пользователь авторизован, сохраняем в БД
        if ($request->user()) {
            $user = $request->user();
            $user->locale = $locale;
            $user->save();
            \Log::info('Locale saved to user', [
                'user_id' => $user->id,
                'user_locale' => $user->locale,
                'user_refreshed' => $user->refresh()->locale
            ]);
        }
        
        // Устанавливаем текущий язык
        App::setLocale($locale);
        
        \Log::info('Locale change completed', [
            'locale' => $locale,
            'session_locale' => Session::get('locale'),
            'app_locale' => App::getLocale(),
            'user_id' => $request->user()?->id
        ]);
        
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

