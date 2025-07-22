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
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->uuid('assignment_id')->nullable();
            $table->uuid('workshop_id')->nullable();
            $table->string('status')->nullable();
            $table->string('assignment_type')->nullable();
            $table->boolean('qr_status')->default(false);
            $table->integer('order')->default(0);
            $table->integer('pin_code')->nullable();
            $table->decimal('total_points', 10)->nullable();
            $table->bigInteger('rank')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};
