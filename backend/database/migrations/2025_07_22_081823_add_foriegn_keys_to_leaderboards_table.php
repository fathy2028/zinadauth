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
        Schema::table('leaderboards', function (Blueprint $table) {
            $table->foreign('user_id', 'leaderboards_ibfk_1')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('assignment_id', 'leaderboards_ibfk_2')->references('id')->on('assignments')->cascadeOnDelete();
            $table->foreign('workshop_id', 'leaderboards_ibfk_3')->references('id')->on('workshops')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaderboards', function (Blueprint $table) {
            $table->dropForeign('leaderboards_ibfk_1');
            $table->dropForeign('leaderboards_ibfk_2');
            $table->dropForeign('leaderboards_ibfk_3');
        });
    }
};
