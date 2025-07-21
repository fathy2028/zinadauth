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
        Schema::table('content_workshops', function (Blueprint $table) {
            $table->foreign(['content_id'], 'content_workshops_ibfk_1')->references(['id'])->on('contents')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['workshop_id'], 'content_workshops_ibfk_2')->references(['id'])->on('workshops')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_workshops', function (Blueprint $table) {
            $table->dropForeign('content_workshops_ibfk_1');
            $table->dropForeign('content_workshops_ibfk_2');
        });
    }
};
