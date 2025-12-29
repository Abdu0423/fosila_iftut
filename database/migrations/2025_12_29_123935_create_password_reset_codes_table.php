<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_reset_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index()->comment('Номер телефона');
            $table->string('code', 6)->comment('Код подтверждения');
            $table->timestamp('expires_at')->comment('Время истечения кода (5 минут)');
            $table->timestamp('last_sent_at')->nullable()->comment('Время последней отправки SMS');
            $table->boolean('used')->default(false)->comment('Использован ли код');
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['phone', 'code']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_codes');
    }
};
