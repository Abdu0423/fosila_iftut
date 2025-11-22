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
                'locale' => $user->locale ?? 'tg',
            ];
            
            \Log::info('HandleInertiaRequests: Sharing user data', [
                'user_data' => $userData
            ]);
        }
        
        $currentLocale = app()->getLocale();
        
        \Log::info('HandleInertiaRequests: Sharing props', [
            'locale' => $currentLocale,
            'config_locale' => config('app.locale'),
            'user_locale' => $user ? $user->locale : null,
            'session_locale' => $request->session()->get('locale'),
        ]);
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userData,
            ],
            'locale' => $currentLocale,
            'translations' => $this->getTranslations(),
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
     * Получить переводы для текущей локали
     */
    protected function getTranslations(): array
    {
        $locale = app()->getLocale();
        
        // Проверяем, что locale валидный
        if (!in_array($locale, ['ru', 'tg'])) {
            \Log::warning('Invalid locale detected, falling back to tg', [
                'invalid_locale' => $locale,
                'app_locale' => app()->getLocale(),
                'config_locale' => config('app.locale'),
                'env_locale' => env('APP_LOCALE')
            ]);
            $locale = 'tg';
        }
        
        $translationFiles = ['auth', 'validation', 'messages', 'dashboard', 'navigation', 
                            'courses', 'lessons', 'tests', 'grades', 'students', 
                            'teachers', 'schedule', 'education_department'];
        
        \Log::info('HandleInertiaRequests: Loading translations', [
            'locale' => $locale,
            'app_locale' => app()->getLocale(),
            'config_locale' => config('app.locale'),
            'files' => $translationFiles
        ]);
        
        $translations = [];
        foreach ($translationFiles as $file) {
            $filePath = base_path("lang/{$locale}/{$file}.php");
            if (file_exists($filePath)) {
                $translations[$file] = include $filePath;
                $keysCount = is_array($translations[$file]) ? count($translations[$file]) : 0;
                \Log::info("Loaded translation file: {$file}", [
                    'locale' => $locale,
                    'keys_count' => $keysCount,
                    'sample_key' => $file === 'navigation' ? ($translations[$file]['dashboard'] ?? 'NOT FOUND') : 'N/A'
                ]);
            } else {
                $translations[$file] = [];
                \Log::warning("Translation file not found: {$file}", [
                    'locale' => $locale,
                    'path' => $filePath
                ]);
            }
        }
        
        return $translations;
    }
}
