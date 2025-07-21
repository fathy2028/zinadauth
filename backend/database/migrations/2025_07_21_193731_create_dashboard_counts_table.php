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
        Schema::create('dashboard_counts', function (Blueprint $table) {
            $table->integer('id')->nullable();
            $table->bigInteger('admin_count')->nullable();
            $table->bigInteger('participant_count')->nullable();
            $table->bigInteger('facilitator_count')->nullable();
            $table->bigInteger('assignment_count')->nullable();
            $table->bigInteger('workshop_count')->nullable();
            $table->bigInteger('question_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_counts');
    }
};
