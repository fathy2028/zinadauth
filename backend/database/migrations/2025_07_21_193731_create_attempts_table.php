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
        Schema::create('attempts', function (Blueprint $table) {
            $table->char('id', 36)->default('uuid()')->primary();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->text('text_answer')->nullable();
            $table->smallInteger('choice_answer')->nullable();
            $table->char('question_id', 36)->nullable()->index('question_id');
            $table->char('user_id', 36)->nullable()->index('user_id');
            $table->char('assignment_workshop_id', 36)->nullable()->index('assignment_workshop_id');
            $table->json('result')->nullable();
            $table->integer('step')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
