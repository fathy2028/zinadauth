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
        Schema::create('content_workshops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('content_id')->index('content_id');
            $table->uuid('workshop_id')->index('workshop_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_workshops');
    }
};
