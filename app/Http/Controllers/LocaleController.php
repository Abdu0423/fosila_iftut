<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

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
        
        // Возвращаем редирект с установкой cookie
        $response = redirect()->back();
        $response->cookie('locale', $locale, 60 * 24 * 365, '/', null, false, false); // 1 год
        
        return $response;
    }
}

