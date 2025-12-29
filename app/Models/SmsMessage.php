<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'message',
        'sender',
        'status',
        'response',
        'error',
        'user_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Связь с пользователем
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Пометить как отправленное
     */
    public function markAsSent($response = null)
    {
        $this->update([
            'status' => 'sent',
            'response' => $response,
            'sent_at' => now(),
        ]);
    }

    /**
     * Пометить как неудачное
     */
    public function markAsFailed($error)
    {
        $this->update([
            'status' => 'failed',
            'error' => $error,
        ]);
    }
}
