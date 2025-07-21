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
        Schema::create('user_workshops', function (Blueprint $table) {
            $table->char('id', 36)->default('uuid()')->primary();
            $table->char('user_id', 36)->nullable()->index('user_id');
            $table->char('workshop_id', 36)->nullable()->index('workshop_id');
            $table->enum('status', ['not_exist', 'not_attend', 'attend'])->nullable()->default('not_attend');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_workshops');
    }
};
