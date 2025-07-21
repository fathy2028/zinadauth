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
        Schema::table('assignment_attendance', function (Blueprint $table) {
            $table->foreign(['user_id'], 'assignment_attendance_ibfk_1')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['assignment_workshop_id'], 'assignment_attendance_ibfk_2')->references(['id'])->on('assignment_workshops')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_attendance', function (Blueprint $table) {
            $table->dropForeign('assignment_attendance_ibfk_1');
            $table->dropForeign('assignment_attendance_ibfk_2');
        });
    }
};
