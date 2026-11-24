<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('groups')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `groups` MODIFY `department_id` BIGINT UNSIGNED NULL, MODIFY `specialty_id` BIGINT UNSIGNED NULL, MODIFY `course` INT NULL DEFAULT 1');
        }
    }

    public function down(): void
    {
        // Откат не выполняется, чтобы не нарушать существующие записи с пустыми связями.
    }
};
