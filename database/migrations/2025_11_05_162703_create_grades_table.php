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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            
            // 5 оценок для каждого студента по каждому расписанию
            $table->decimal('grade_1', 5, 2)->nullable()->comment('Оценка 1 - ставит учитель');
            $table->decimal('grade_2', 5, 2)->nullable()->comment('Оценка 2 - ставит учитель');
            $table->decimal('grade_3', 5, 2)->nullable()->comment('Оценка 3 - из теста');
            $table->decimal('grade_4', 5, 2)->nullable()->comment('Оценка 4 - из теста');
            $table->decimal('grade_5', 5, 2)->nullable()->comment('Оценка 5 - экзамен из теста');
            
            $table->timestamps();
            
            // Уникальность: один студент может иметь только один набор оценок по расписанию
            $table->unique(['schedule_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
