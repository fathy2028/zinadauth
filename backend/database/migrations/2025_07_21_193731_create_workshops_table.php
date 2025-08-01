<?php

use App\Enums\WorkshopStatusTypeEnum;
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
            $table->uuid('id')->primary();
            $table->text('title');
            $table->text('description');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->uuid('created_by');
            $table->uuid('setting_id')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('qr_status')->default(true);
            $table->enum('status', WorkshopStatusTypeEnum::values())->default(WorkshopStatusTypeEnum::INACTIVE->value);
            $table->integer('pin_code')->default(rand(900000, 1000000))->unique();
            $table->timestamps();
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
