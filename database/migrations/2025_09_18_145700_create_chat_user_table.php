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
        if (Schema::hasTable('chat_user')) {
            return;
        }
        
        Schema::create('chat_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['admin', 'member'])->default('member'); // Роль в чате
            $table->timestamp('joined_at')->useCurrent(); // Время присоединения к чату
            $table->timestamp('last_read_at')->nullable(); // Время последнего прочтения
            $table->boolean('is_muted')->default(false); // Заглушен ли пользователь
            $table->boolean('is_active')->default(true); // Активен ли в чате
            $table->timestamps();
            
            $table->unique(['chat_id', 'user_id']); // Уникальная связь
            $table->index(['user_id', 'last_read_at']); // Индекс для быстрого поиска
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_user');
    }
};
