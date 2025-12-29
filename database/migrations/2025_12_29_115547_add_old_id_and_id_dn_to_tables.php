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
        // Добавляем old_id в таблицу specialties
        if (Schema::hasTable('specialties')) {
            Schema::table('specialties', function (Blueprint $table) {
                $table->unsignedBigInteger('old_id')->nullable()->after('id');
                $table->index('old_id');
            });
        }

        // Добавляем old_id в таблицу subjects
        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->unsignedBigInteger('old_id')->nullable()->after('id');
                $table->index('old_id');
            });
        }

        // Добавляем old_id и id_dn в таблицу users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('old_id')->nullable()->after('id');
                $table->string('id_dn', 255)->nullable()->after('old_id');
                $table->index('old_id');
                $table->index('id_dn');
            });
        }

        // Добавляем old_id в таблицу groups
        if (Schema::hasTable('groups')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->unsignedBigInteger('old_id')->nullable()->after('id');
                $table->index('old_id');
            });
        }

        // Добавляем old_id в таблицу grades
        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->unsignedBigInteger('old_id')->nullable()->after('id');
                $table->index('old_id');
            });
        }

        // Добавляем old_id в таблицу schedules
        if (Schema::hasTable('schedules')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->unsignedBigInteger('old_id')->nullable()->after('id');
                $table->index('old_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем old_id из таблицы specialties
        if (Schema::hasTable('specialties')) {
            Schema::table('specialties', function (Blueprint $table) {
                $table->dropIndex(['old_id']);
                $table->dropColumn('old_id');
            });
        }

        // Удаляем old_id из таблицы subjects
        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropIndex(['old_id']);
                $table->dropColumn('old_id');
            });
        }

        // Удаляем old_id и id_dn из таблицы users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['old_id']);
                $table->dropIndex(['id_dn']);
                $table->dropColumn(['old_id', 'id_dn']);
            });
        }

        // Удаляем old_id из таблицы groups
        if (Schema::hasTable('groups')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropIndex(['old_id']);
                $table->dropColumn('old_id');
            });
        }

        // Удаляем old_id из таблицы grades
        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->dropIndex(['old_id']);
                $table->dropColumn('old_id');
            });
        }

        // Удаляем old_id из таблицы schedules
        if (Schema::hasTable('schedules')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropIndex(['old_id']);
                $table->dropColumn('old_id');
            });
        }
    }
};
