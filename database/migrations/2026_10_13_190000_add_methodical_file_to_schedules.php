<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('methodical_path')->nullable()->after('has_coursework');
            $table->string('methodical_name')->nullable()->after('methodical_path');
            $table->string('methodical_mime')->nullable()->after('methodical_name');
            $table->unsignedBigInteger('methodical_size')->nullable()->after('methodical_mime');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn([
                'methodical_path',
                'methodical_name',
                'methodical_mime',
                'methodical_size',
            ]);
        });
    }
};
