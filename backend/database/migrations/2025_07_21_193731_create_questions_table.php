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
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('question_text');
            $table->text('question_text_ar');
            $table->json('choices');
            $table->json('choices_ar');
            $table->enum('type', \App\Enums\QuestionTypeEnum::values());
            $table->uuid('created_by');
            $table->bigInteger('points')->default(0);
            $table->integer('duration')->default(3);
            $table->smallInteger('answer');
            $table->text('text_answer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
