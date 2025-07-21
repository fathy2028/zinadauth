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
        Schema::create('choices', function (Blueprint $table) {
            $table->char('id', 36)->default('uuid()')->primary();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->json('choices')->nullable();
            $table->json('choices_ar')->nullable();
            $table->enum('type', ['standard', 'custom'])->nullable();
            $table->integer('duration')->nullable()->default(3);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('choices');
    }
};
