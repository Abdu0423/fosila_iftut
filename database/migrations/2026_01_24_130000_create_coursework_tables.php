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
        // Добавляем поле has_coursework в таблицу schedules
        Schema::table('schedules', function (Blueprint $table) {
            $table->boolean('has_coursework')->default(false)->after('is_active');
        });

        // Таблица курсовых работ (настройки для расписания)
        Schema::create('coursework_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->string('title')->nullable(); // Название курсовой работы
            $table->text('description')->nullable(); // Описание
            $table->text('requirements')->nullable(); // Требования
            $table->date('deadline')->nullable(); // Срок сдачи
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('schedule_id'); // Одна курсовая на расписание
        });

        // Таблица тем курсовых работ
        Schema::create('coursework_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coursework_task_id')->constrained('coursework_tasks')->onDelete('cascade');
            $table->text('topic_text');
            $table->foreignId('taken_by_student_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('taken_at')->nullable();
            $table->timestamps();
            
            $table->index('coursework_task_id');
        });

        // Таблица работ студентов
        Schema::create('coursework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coursework_task_id')->constrained('coursework_tasks')->onDelete('cascade');
            $table->foreignId('topic_id')->constrained('coursework_topics')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->longText('text')->nullable(); // Текст работы
            $table->string('file_path')->nullable(); // Путь к файлу
            $table->string('file_name')->nullable(); // Имя файла
            $table->integer('file_size')->nullable(); // Размер файла
            $table->text('teacher_comment')->nullable(); // Комментарий учителя
            $table->decimal('grade_100', 5, 2)->nullable(); // Оценка 100-балльная
            $table->decimal('grade_10', 3, 1)->nullable(); // Оценка 10-балльная
            $table->string('grade_letter', 5)->nullable(); // Буквенная оценка
            $table->enum('status', ['draft', 'submitted', 'checked', 'returned'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
            
            $table->unique(['coursework_task_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coursework_submissions');
        Schema::dropIfExists('coursework_topics');
        Schema::dropIfExists('coursework_tasks');
        
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('has_coursework');
        });
    }
};
