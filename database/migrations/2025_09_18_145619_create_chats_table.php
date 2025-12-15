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
        if (Schema::hasTable('chats')) {
            return;
        }
        
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Название чата (для групповых чатов)
            $table->enum('type', ['private', 'group'])->default('private'); // Тип чата
            $table->text('description')->nullable(); // Описание чата
            $table->string('avatar')->nullable(); // Аватар чата
            $table->boolean('is_active')->default(true); // Активен ли чат
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
