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
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->comment('Номер телефона получателя');
            $table->text('message')->comment('Текст SMS сообщения');
            $table->string('sender', 50)->nullable()->comment('Отправитель (sender name)');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->comment('Статус отправки');
            $table->text('response')->nullable()->comment('Ответ от API OsonSMS');
            $table->text('error')->nullable()->comment('Текст ошибки, если была');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Связанный пользователь');
            $table->timestamp('sent_at')->nullable()->comment('Время отправки');
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index('phone');
            $table->index('status');
            $table->index('user_id');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
