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
        Schema::table('template_links', function (Blueprint $table) {
            $table->foreign(['template_id'], 'template_links_ibfk_1')->references(['id'])->on('templates')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_links', function (Blueprint $table) {
            $table->dropForeign('template_links_ibfk_1');
        });
    }
};
