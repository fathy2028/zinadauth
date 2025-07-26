<?php

use App\Enums\WorkshopStatusTypeEnum;
use App\Enums\AssignmentWorkshopTypeEnum;
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
            $table->uuid('id')->primary();
            $table->uuid('assignment_id');
            $table->uuid('workshop_id');
            $table->enum('status', WorkshopStatusTypeEnum::values())->default(WorkshopStatusTypeEnum::INACTIVE->value);
            $table->enum('assignment_type', AssignmentWorkshopTypeEnum::values())->default(AssignmentWorkshopTypeEnum::INTERACTIVE->value);
            $table->boolean('qr_status')->default(true);
            $table->decimal('order_num', 10)->default(0);
            $table->timestamps();
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
