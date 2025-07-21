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
        Schema::create('template_links', function (Blueprint $table) {
            $table->char('id', 36)->default('uuid()')->primary();
            $table->char('template_id', 36)->nullable();
            $table->string('linked_entity_type')->nullable();
            $table->char('linked_entity_id', 36)->nullable();

            $table->index(['template_id', 'linked_entity_type', 'linked_entity_id'], 'template_links_index_0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_links');
    }
};
