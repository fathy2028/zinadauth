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
        Schema::create('workshops', function (Blueprint $table) {
            $table->char('id', 36)->default('uuid()')->primary();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->char('created_by', 36)->nullable()->index('created_by');
            $table->char('setting_id', 36)->nullable()->index('setting_id');
            $table->boolean('is_deleted')->nullable()->default(false);
            $table->boolean('qr_status')->nullable()->default(true);
            $table->enum('status', ['active', 'inactive', 'loading'])->nullable()->default('inactive');
            $table->integer('pin_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
