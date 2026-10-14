<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('user_name')->nullable();
            $table->string('role_name')->nullable();
            $table->string('action', 16)->index();
            $table->string('table_name')->index();
            $table->string('record_id')->nullable()->index();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('url', 1000)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_logs');
    }
};
