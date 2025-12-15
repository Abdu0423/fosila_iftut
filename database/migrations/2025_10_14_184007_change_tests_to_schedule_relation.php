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
        // Проверяем есть ли уже колонка schedule_id
        if (Schema::hasColumn('tests', 'schedule_id')) {
            return;
        }
        
        // Отключаем проверку внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        Schema::table('tests', function (Blueprint $table) {
            // Удаляем внешний ключ на lessons если есть
            if (Schema::hasColumn('tests', 'lesson_id')) {
                try {
                    $table->dropForeign(['lesson_id']);
                } catch (\Exception $e) {
                    // Игнорируем если внешнего ключа нет
                }
                $table->dropColumn('lesson_id');
            }
            
            // Добавляем колонку schedule_id (nullable чтобы избежать ошибок с существующими данными)
            $table->unsignedBigInteger('schedule_id')->nullable()->after('description');
        });
        
        // Удаляем ненужные колонки если они есть
        Schema::table('tests', function (Blueprint $table) {
            $columns = ['type', 'is_public', 'start_date', 'end_date'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        
        // Добавляем внешний ключ отдельно
        try {
            Schema::table('tests', function (Blueprint $table) {
                $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Игнорируем если ключ уже есть
        }
        
        // Включаем проверку внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        Schema::table('tests', function (Blueprint $table) {
            if (Schema::hasColumn('tests', 'schedule_id')) {
                try {
                    $table->dropForeign(['schedule_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('schedule_id');
            }
            
            if (!Schema::hasColumn('tests', 'lesson_id')) {
                $table->unsignedBigInteger('lesson_id')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('tests', 'type')) {
                $table->string('type')->default('quiz');
            }
            if (!Schema::hasColumn('tests', 'is_public')) {
                $table->boolean('is_public')->default(false);
            }
            if (!Schema::hasColumn('tests', 'start_date')) {
                $table->datetime('start_date')->nullable();
            }
            if (!Schema::hasColumn('tests', 'end_date')) {
                $table->datetime('end_date')->nullable();
            }
        });
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
