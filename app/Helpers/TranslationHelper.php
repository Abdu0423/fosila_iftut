<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class TranslationHelper
{
    /**
     * Получить перевод из JSON файла
     * Использует формат: TranslationHelper::get('navigation.dashboard')
     */
    public static function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        
        // Проверяем, что язык поддерживается
        if (!in_array($locale, ['ru', 'tg'])) {
            $locale = 'ru';
        }
        
        $filePath = lang_path("{$locale}.json");
        
        if (!File::exists($filePath)) {
            return $key;
        }
        
        $content = File::get($filePath);
        $translations = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($translations)) {
            return $key;
        }
        
        // Разбиваем ключ на части (например, 'navigation.dashboard')
        $keys = explode('.', $key);
        $value = $translations;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $key;
            }
            $value = $value[$k];
        }
        
        // Заменяем плейсхолдеры
        if (!empty($replace)) {
            foreach ($replace as $placeholder => $replacement) {
                $value = str_replace(":{$placeholder}", $replacement, $value);
            }
        }
        
        return $value;
    }
}

