<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $unreadChatsCount = 0;
        
        if ($user) {
            // Загружаем роль, если она не загружена
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }
            
            // Логируем для отладки
            \Log::info('HandleInertiaRequests: User role check', [
                'user_id' => $user->id,
                'role_loaded' => $user->relationLoaded('role'),
                'role_id' => $user->role_id,
                'role_name' => $user->role ? $user->role->name : 'NULL',
                'role_object' => $user->role ? get_class($user->role) : 'NULL'
            ]);
            
            // Подсчитываем непрочитанные сообщения для всех чатов пользователя
            $chats = $user->chats()
                ->wherePivot('is_active', true)
                ->get();
            
            foreach ($chats as $chat) {
                $lastReadAt = $chat->users()->where('user_id', $user->id)->first()?->pivot?->last_read_at;
                
                if (!$lastReadAt) {
                    $unreadChatsCount += $chat->messages()->count();
                } else {
                    $unreadChatsCount += $chat->messages()->where('created_at', '>', $lastReadAt)->count();
                }
            }
        }
        
        $userData = null;
        if ($user) {
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? $user->role->name : null,
                'locale' => $user->locale ?? 'ru',
            ];
            
            \Log::info('HandleInertiaRequests: Sharing user data', [
                'user_data' => $userData
            ]);
        }
        
        // Загружаем переводы для текущей локали
        $locale = app()->getLocale();
        $translations = $this->loadTranslations($locale);
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userData,
            ],
            'locale' => $locale,
            'translations' => $translations,
            'unreadChatsCount' => $unreadChatsCount,
            'flash' => [
                'success' => fn () => $request->session()->get('success') ?? null,
                'error' => fn () => $request->session()->get('error') ?? null,
            ],
            'errors' => function () {
                return collect($this->errors)->map(function ($error) {
                    return $error[0];
                })->toArray();
            },
            'ziggy' => function () use ($request) {
                return array_merge((new \Tightenco\Ziggy\Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ];
    }
    
    /**
     * Загрузить переводы для указанной локали из JSON файла
     */
    protected function loadTranslations(string $locale): array
    {
        $locale = in_array($locale, ['ru', 'tg']) ? $locale : 'ru';
        $filePath = lang_path("{$locale}.json");
        
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $translations = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error('Failed to parse translation JSON', [
                    'locale' => $locale,
                    'file' => $filePath,
                    'error' => json_last_error_msg()
                ]);
                return [];
            }
            
            return $translations ?: [];
        }
        
        \Log::warning('Translation file not found', [
            'locale' => $locale,
            'file' => $filePath
        ]);
        
        return [];
    }
}
