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
        
        // Сохраняем в сессии
        Session::put('locale', $locale);
        
        // Если пользователь авторизован, сохраняем в БД
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }
        
        // Устанавливаем текущий язык
        App::setLocale($locale);
        
        return back()->with('success', __('messages.saved_successfully'));
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

