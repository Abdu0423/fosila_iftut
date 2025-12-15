<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Установить язык
     */
    public function setLocale(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:ru,tg'
        ]);
        
        $locale = $request->locale;
        
        // Устанавливаем локаль сразу
        App::setLocale($locale);
        
        // Сохраняем в сессию
        Session::put('locale', $locale);
        Session::save();
        
        // Создаем cookie через очередь cookie
        Cookie::queue('locale', $locale, 60 * 24 * 365); // 1 год
        
        // Если пользователь авторизован, сохраняем в базу
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }
        
        // Возвращаем редирект
        return redirect()->back();
    }
}
