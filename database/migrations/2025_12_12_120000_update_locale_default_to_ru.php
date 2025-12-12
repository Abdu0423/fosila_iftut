<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Изменяем значение по умолчанию на 'ru'
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 2)->default('ru')->change();
        });
        
        // Обновляем все существующие записи с 'tg' на 'ru'
        DB::table('users')->where('locale', 'tg')->update(['locale' => 'ru']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем значение по умолчанию на 'tg'
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 2)->default('tg')->change();
        });
    }
};

