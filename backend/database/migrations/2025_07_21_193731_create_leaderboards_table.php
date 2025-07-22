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
            $table->uuid('user_id');
            $table->uuid('assignment_id');
            $table->uuid('workshop_id');
            $table->string('status');
            $table->string('assignment_type');
            $table->boolean('qr_status')->default(false);
            $table->integer('order')->default(0);
            $table->integer('pin_code');
            $table->decimal('total_points', 10);
            $table->bigInteger('rank');
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
