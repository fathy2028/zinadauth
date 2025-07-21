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
        Schema::table('attempts', function (Blueprint $table) {
            $table->foreign(['question_id'], 'attempts_ibfk_1')->references(['id'])->on('questions')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['user_id'], 'attempts_ibfk_2')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['assignment_workshop_id'], 'attempts_ibfk_3')->references(['id'])->on('assignment_workshops')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropForeign('attempts_ibfk_1');
            $table->dropForeign('attempts_ibfk_2');
            $table->dropForeign('attempts_ibfk_3');
        });
    }
};
