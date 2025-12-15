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
        if (Schema::hasTable('chat_messages')) {
            return;
        }
        
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message'); // Текст сообщения
            $table->string('message_type')->default('text'); // Тип сообщения (text, image, file, etc.)
            $table->json('metadata')->nullable(); // Дополнительные данные (файлы, изображения)
            $table->boolean('is_read')->default(false); // Прочитано ли сообщение
            $table->timestamp('read_at')->nullable(); // Время прочтения
            $table->boolean('is_edited')->default(false); // Редактировалось ли сообщение
            $table->timestamp('edited_at')->nullable(); // Время редактирования
            $table->timestamps();
            
            $table->index(['chat_id', 'created_at']); // Индекс для быстрого поиска сообщений
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
