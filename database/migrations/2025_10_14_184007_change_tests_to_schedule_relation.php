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
        Schema::table('tests', function (Blueprint $table) {
            // Удаляем внешний ключ на lessons
            $table->dropForeign(['lesson_id']);
            
            // Удаляем колонку lesson_id
            $table->dropColumn('lesson_id');
            
            // Добавляем внешний ключ на schedules
            $table->foreignId('schedule_id')->after('description')->constrained()->onDelete('cascade');
            
            // Упрощаем структуру - убираем ненужные поля
            $table->dropColumn(['type', 'is_public', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.m 
     */
    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            // Возвращаем всё обратно
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
            
            $table->foreignId('lesson_id')->after('description')->constrained()->onDelete('cascade');
            
            $table->enum('type', ['quiz', 'exam', 'homework', 'practice'])->default('quiz');
            $table->boolean('is_public')->default(false);
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
        });
    }
};
