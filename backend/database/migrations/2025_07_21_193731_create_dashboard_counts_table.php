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
            $table->id();
            $table->bigInteger('admin_count');
            $table->bigInteger('participant_count');
            $table->bigInteger('facilitator_count');
            $table->bigInteger('assignment_count');
            $table->bigInteger('workshop_count');
            $table->bigInteger('question_count');
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
