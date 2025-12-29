<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'code',
        'expires_at',
        'last_sent_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Проверить, действителен ли код
     */
    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }

    /**
     * Пометить код как использованный
     */
    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    /**
     * Удалить устаревшие коды
     */
    public static function cleanExpired(): void
    {
        static::where('expires_at', '<', now())->delete();
    }

    /**
     * Проверить, можно ли отправить новый код (прошла ли 1 минута)
     */
    public static function canSendNewCode(string $phone): bool
    {
        $lastCode = static::where('phone', $phone)
            ->where('last_sent_at', '>=', now()->subMinute())
            ->first();
        
        return $lastCode === null;
    }
}
