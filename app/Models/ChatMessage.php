<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'message',
        'message_type',
        'metadata',
        'is_read',
        'read_at',
        'is_edited',
        'edited_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'is_edited' => 'boolean',
        'read_at' => 'datetime',
        'edited_at' => 'datetime',
    ];

    /**
     * Чат, к которому принадлежит сообщение
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Автор сообщения
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить отформатированное время сообщения
     */
    public function getFormattedTimeAttribute()
    {
        return $this->created_at->format('H:i');
    }

    /**
     * Получить отформатированную дату сообщения
     */
    public function getFormattedDateAttribute()
    {
        $local = $this->created_at->copy()->timezone('Asia/Dushanbe');

        if ($local->isToday()) {
            return 'Сегодня';
        } elseif ($local->isYesterday()) {
            return 'Вчера';
        } else {
            return $local->format('d.m.Y');
        }
    }

    /**
     * Проверить, является ли сообщение от текущего пользователя
     */
    public function getIsFromCurrentUserAttribute()
    {
        return $this->user_id === auth()->id();
    }

    /**
     * Получить статус сообщения
     */
    public function getStatusAttribute()
    {
        if ($this->is_edited) {
            return 'edited';
        }
        
        if ($this->is_read) {
            return 'read';
        }
        
        return 'sent';
    }
}
