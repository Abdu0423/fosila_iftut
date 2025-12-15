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
        if (Schema::hasTable('test_answers')) {
            return;
        }
        
        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('test_questions')->onDelete('cascade');
            $table->text('answer');
            $table->boolean('is_correct')->default(false);
            $table->integer('order')->default(0);
            $table->string('matching_key')->nullable(); // ключ для соответствия (для вопросов на соответствие)
            $table->string('matching_value')->nullable(); // значение для соответствия (для вопросов на соответствие)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_answers');
    }
};
