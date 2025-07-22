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
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('client');
            $table->text('logo');
            $table->text('primary_color');
            $table->text('secondary_color');
            $table->text('dark_primary_color');
            $table->text('dark_secondary_color');
            $table->text('lang');
            $table->text('domain_name');
            $table->double('duration')->default(0.5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
