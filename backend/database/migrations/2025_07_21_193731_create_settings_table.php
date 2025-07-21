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
            $table->char('id', 36)->default('uuid()')->primary();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->text('client')->nullable();
            $table->text('logo')->nullable();
            $table->text('primary_color')->nullable();
            $table->text('secondary_color')->nullable();
            $table->text('dark_primary_color')->nullable();
            $table->text('dark_secondary_color')->nullable();
            $table->text('lang')->nullable();
            $table->text('domain_name')->nullable();
            $table->decimal('duration', 10)->nullable()->default(0.5);
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
