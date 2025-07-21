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
        Schema::table('assignment_questions', function (Blueprint $table) {
            $table->foreign(['assignment_id'], 'assignment_questions_ibfk_1')->references(['id'])->on('assignments')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['question_id'], 'assignment_questions_ibfk_2')->references(['id'])->on('questions')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_questions', function (Blueprint $table) {
            $table->dropForeign('assignment_questions_ibfk_1');
            $table->dropForeign('assignment_questions_ibfk_2');
        });
    }
};
