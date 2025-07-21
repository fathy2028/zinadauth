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
            $table->text('question_text_ar')->nullable();
            $table->json('choices')->nullable();
            $table->json('choices_ar')->nullable();
            $table->enum('type', \App\Enums\QuestionTypeEnum::values())->nullable();
            $table->char('created_by', 36)->nullable()->index('created_by');
            $table->bigInteger('points')->nullable()->default(0);
            $table->integer('duration')->nullable()->default(3);
            $table->smallInteger('answer')->nullable();
            $table->text('text_answer')->nullable();
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
