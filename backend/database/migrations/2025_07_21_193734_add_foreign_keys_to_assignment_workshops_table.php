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
        Schema::table('assignment_workshops', function (Blueprint $table) {
            $table->foreign(['assignment_id'], 'assignment_workshops_ibfk_1')->references(['id'])->on('assignments')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['workshop_id'], 'assignment_workshops_ibfk_2')->references(['id'])->on('workshops')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_workshops', function (Blueprint $table) {
            $table->dropForeign('assignment_workshops_ibfk_1');
            $table->dropForeign('assignment_workshops_ibfk_2');
        });
    }
};
