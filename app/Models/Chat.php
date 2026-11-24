<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'avatar',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'display_avatar',
    ];

    /**
     * Пользователи в чате
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_user')
                    ->withPivot(['role', 'joined_at', 'last_read_at', 'is_muted', 'is_active'])
                    ->withTimestamps();
    }

    /**
     * Сообщения в чате
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Последнее сообщение в чате
     */
    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class)->latest();
    }

    /**
     * Получить название чата для отображения
     */
    public function getDisplayNameAttribute()
    {
        if ($this->type === 'group' && $this->name) {
            return $this->name;
        }

        // Для приватных чатов возвращаем имя собеседника
        if ($this->type === 'private') {
            $otherUser = $this->users()->where('user_id', '!=', auth()->id())->first();
            return $otherUser ? $otherUser->name : 'Неизвестный пользователь';
        }

        return $this->name ?: 'Безымянный чат';
    }

    /**
     * Получить аватар чата
     */
    public function getDisplayAvatarAttribute()
    {
        $own = $this->attributes['avatar'] ?? null;
        if ($own) {
            return $this->fileUrl($own);
        }

        $others = $this->relationLoaded('users')
            ? $this->users
            : $this->users()->where('user_id', '!=', auth()->id())->get();

        $other = collect($others)->first(function ($person) {
            $id = is_array($person) ? ($person['id'] ?? null) : $person->id;

            return (int) $id !== (int) auth()->id();
        });

        if (! $other) {
            return null;
        }

        if (is_array($other)) {
            return $other['avatar'] ?? null;
        }

        return $other->avatarUrl();
    }

    protected function fileUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Количество непрочитанных сообщений для текущего пользователя
     */
    public function getUnreadCountAttribute()
    {
        $user = auth()->user();
        if (!$user) return 0;

        $lastReadAt = $this->users()->where('user_id', $user->id)->first()?->pivot?->last_read_at;
        
        if (!$lastReadAt) {
            return $this->messages()->count();
        }

        return $this->messages()->where('created_at', '>', $lastReadAt)->count();
    }
}
