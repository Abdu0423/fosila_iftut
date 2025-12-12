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
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userData,
            ],
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
    
}
