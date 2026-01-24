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
        Schema::table('grades', function (Blueprint $table) {
            $table->boolean('rating_1_closed')->default(false)->after('rating_teacher_1');
            $table->boolean('rating_2_closed')->default(false)->after('rating_teacher_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['rating_1_closed', 'rating_2_closed']);
        });
    }
};
