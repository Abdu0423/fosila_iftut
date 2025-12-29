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
        
        // Если пользователь авторизован, пробуем сохранить в базу
        try {
            $user = $request->user();
            if ($user && method_exists($user, 'update')) {
                $user->locale = $locale;
                $user->save();
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки сохранения в базу - cookie и session достаточно
            \Log::warning('Failed to save user locale to database', ['error' => $e->getMessage()]);
        }
        
        // Определяем название языка для сообщения
        $localeName = $locale === 'ru' ? 'Русский' : 'Тоҷикӣ';
        
        // Для Inertia запросов всегда возвращаем redirect с flash сообщением
        // Inertia автоматически обработает это как успешный ответ
        return redirect()->back()->with('success', "Язык изменен на {$localeName}");
    }
}
