<?php

use App\Enums\AttendanceTypeEnum;
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
        Schema::create('attendance', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('workshop_id');
            $table->integer('pin_code');
            $table->enum('status', AttendanceTypeEnum::values())->default(AttendanceTypeEnum::NOT_EXIST->value);
            $table->timestamps();

            $table->primary(['user_id', 'workshop_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
