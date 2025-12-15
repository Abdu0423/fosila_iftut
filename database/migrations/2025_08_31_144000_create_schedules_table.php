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
        if (Schema::hasTable('schedules')) {
            return;
        }
        
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade'); // Ссылка на предмет
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade'); // ID учителя
            $table->foreignId('group_id')->constrained()->onDelete('cascade'); // ID группы
            $table->integer('semester'); // Семестр (1 или 2)
            $table->integer('credits'); // Количество кредитов
            $table->integer('study_year'); // Год обучения
            $table->integer('order')->default(0); // Порядок урока
            $table->datetime('scheduled_at')->nullable(); // Запланированная дата
            $table->boolean('is_active')->default(true); // Статус активности
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
