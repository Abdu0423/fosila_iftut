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
        Schema::table('schedules', function (Blueprint $table) {
            // Дата начала первого дистанционного рубежа
            $table->date('distance_control_1_date')->nullable()->after('scheduled_at');
            // Дата начала второго дистанционного рубежа
            $table->date('distance_control_2_date')->nullable()->after('distance_control_1_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['distance_control_1_date', 'distance_control_2_date']);
        });
    }
};
