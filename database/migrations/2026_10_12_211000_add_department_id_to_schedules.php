<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        DB::statement('ALTER TABLE schedules MODIFY teacher_id BIGINT UNSIGNED NULL');

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('teacher_id')->constrained('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropForeign(['teacher_id']);
        });

        DB::statement('UPDATE schedules SET teacher_id = 1 WHERE teacher_id IS NULL');
        DB::statement('ALTER TABLE schedules MODIFY teacher_id BIGINT UNSIGNED NOT NULL');

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
