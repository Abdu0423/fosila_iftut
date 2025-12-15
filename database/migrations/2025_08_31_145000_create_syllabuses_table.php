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
        if (Schema::hasTable('syllabuses')) {
            return;
        }
        
        Schema::create('syllabuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Имя силлабуса
            $table->text('description')->nullable(); // Описание
            $table->string('file_path')->nullable(); // Путь к файлу
            $table->string('file_name')->nullable(); // Имя файла
            $table->string('file_type')->nullable(); // Тип файла
            $table->integer('file_size')->nullable(); // Размер файла в байтах
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade'); // ID урока
            $table->integer('creation_year'); // Год создания
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Кто создал
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syllabuses');
    }
};
