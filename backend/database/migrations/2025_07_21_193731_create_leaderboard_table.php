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
        Schema::create('leaderboard', function (Blueprint $table) {
            $table->char('user_id', 36)->nullable();
            $table->char('id', 36)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->char('assignment_id', 36)->nullable();
            $table->char('workshops_id', 36)->nullable();
            $table->string('status')->nullable();
            $table->string('assignment_type')->nullable();
            $table->boolean('qr_status')->nullable();
            $table->decimal('order', 10)->nullable();
            $table->integer('pin_code')->nullable();
            $table->decimal('total_points', 10)->nullable();
            $table->bigInteger('rank')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard');
    }
};
