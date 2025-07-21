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
        Schema::create('assignment_attendance', function (Blueprint $table) {
            $table->char('user_id', 36);
            $table->char('assignment_workshop_id', 36)->index('assignment_workshop_id');
            $table->integer('pin_code')->nullable();
            $table->enum('status', ['not_exist', 'not_attend', 'attend'])->nullable()->default('not_exist');

            $table->primary(['user_id', 'assignment_workshop_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_attendance');
    }
};
