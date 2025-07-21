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
        Schema::create('assignment_workshops', function (Blueprint $table) {
            $table->char('id', 36)->default('uuid()')->primary();
            $table->char('assignment_id', 36)->nullable()->index('assignment_id');
            $table->char('workshop_id', 36)->nullable()->index('workshop_id');
            $table->enum('status', ['active', 'inactive', 'loading'])->nullable()->default('inactive');
            $table->enum('assignment_type', ['interactive', 'traditional'])->nullable()->default('interactive');
            $table->boolean('qr_status')->nullable()->default(true);
            $table->decimal('order_num', 10)->nullable()->default(0);
            $table->integer('pin_code')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_workshops');
    }
};
