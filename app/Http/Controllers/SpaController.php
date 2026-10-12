<?php

namespace App\Http\Controllers;

use App\Support\PageAccess;
use Illuminate\Http\Request;

class SpaController extends Controller
{
    /**
     * Текущий пользователь и общие данные сессии для SPA.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $unreadChatsCount = 0;
        $userData = null;

        if ($user) {
            if (! $user->relationLoaded('role')) {
                $user->load('role');
            }

            $chats = $user->chats()->wherePivot('is_active', true)->get();

            foreach ($chats as $chat) {
                $lastReadAt = $chat->users()->where('user_id', $user->id)->first()?->pivot?->last_read_at;

                if (! $lastReadAt) {
                    $unreadChatsCount += $chat->messages()->count();
                } else {
                    $unreadChatsCount += $chat->messages()->where('created_at', '>', $lastReadAt)->count();
                }
            }

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? $user->role->name : null,
                'locale' => $user->locale ?? 'ru',
            ];
        }

        return response()->json([
            'user' => $userData,
            'locale' => app()->getLocale(),
            'unreadChatsCount' => $unreadChatsCount,
            'pages' => PageAccess::grantedKeys($user),
        ]);
    }
}
