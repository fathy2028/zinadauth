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
        Schema::create('contents', function (Blueprint $table) {
            $table->char('id', 36)->default('uuid()')->primary();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->text('title')->nullable();
            $table->text('brief')->nullable();
            $table->text('ppt')->nullable();
            $table->char('created_by', 36)->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
