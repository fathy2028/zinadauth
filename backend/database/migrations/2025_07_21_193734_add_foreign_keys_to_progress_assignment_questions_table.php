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
        Schema::table('progress_assignment_questions', function (Blueprint $table) {
            $table->foreign(['assignment_workshop_id'], 'progress_assignment_questions_ibfk_1')->references(['id'])->on('assignment_workshops')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['choice_id'], 'progress_assignment_questions_ibfk_2')->references(['id'])->on('choices')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_assignment_questions', function (Blueprint $table) {
            $table->dropForeign('progress_assignment_questions_ibfk_1');
            $table->dropForeign('progress_assignment_questions_ibfk_2');
        });
    }
};
