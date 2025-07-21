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
        Schema::create('assignments', function (Blueprint $table) {
            $table->char('id', 36)->default('uuid()')->primary();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->enum('question_order', ['ordered', 'random'])->nullable()->default('ordered');
            $table->char('created_by', 36)->nullable()->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
