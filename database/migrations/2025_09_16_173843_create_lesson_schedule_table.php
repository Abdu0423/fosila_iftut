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
        if (Schema::hasTable('lesson_schedule')) {
            return;
        }
        
        Schema::create('lesson_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0); // Порядок урока в расписании
            $table->integer('duration')->nullable(); // Продолжительность урока в минутах
            $table->time('start_time')->nullable(); // Время начала урока
            $table->time('end_time')->nullable(); // Время окончания урока
            $table->string('room')->nullable(); // Аудитория
            $table->text('notes')->nullable(); // Дополнительные заметки
            $table->timestamps();
            
            // Уникальная связь - один урок может быть в одном расписании только один раз
            $table->unique(['lesson_id', 'schedule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_schedule');
    }
};