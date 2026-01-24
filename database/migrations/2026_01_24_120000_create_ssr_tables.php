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
        // Таблица ССР заданий (учитель создает)
        Schema::create('ssr_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->date('deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Таблица тем ССР (список тем для выбора студентами)
        Schema::create('ssr_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ssr_task_id')->constrained('ssr_tasks')->onDelete('cascade');
            $table->text('topic_text');
            $table->foreignId('taken_by_student_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('taken_at')->nullable();
            $table->timestamps();
            
            // Индекс для быстрого поиска тем задания
            $table->index('ssr_task_id');
        });

        // Таблица работ студентов
        Schema::create('ssr_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ssr_task_id')->constrained('ssr_tasks')->onDelete('cascade');
            $table->foreignId('topic_id')->constrained('ssr_topics')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->longText('text')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('teacher_comment')->nullable();
            $table->decimal('grade', 5, 2)->nullable();
            $table->enum('status', ['draft', 'submitted', 'checked', 'returned'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
            
            // Один студент - одна работа на одно задание
            $table->unique(['ssr_task_id', 'student_id'], 'ssr_submission_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ssr_submissions');
        Schema::dropIfExists('ssr_topics');
        Schema::dropIfExists('ssr_tasks');
    }
};
