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
        Schema::table('questions', function (Blueprint $table) {
            $table->text('question_text_ar')->nullable()->change();
            $table->json('choices')->nullable()->change();
            $table->json('choices_ar')->nullable()->change();
            $table->smallInteger('answer')->nullable()->change();
            $table->text('text_answer')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('question_text_ar')->nullable(false)->change();
            $table->json('choices')->nullable(false)->change();
            $table->json('choices_ar')->nullable(false)->change();
            $table->smallInteger('answer')->nullable(false)->change();
            $table->text('text_answer')->nullable(false)->change();
        });
    }
};
