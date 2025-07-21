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
        Schema::create('progress_assignment_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('assignment_workshop_id', 36)->nullable()->index('assignment_workshop_id');
            $table->char('choice_id', 36)->nullable()->index('choice_id');
            $table->enum('status', WorkshopStatusTypeEnum::values())->nullable()->default(WorkshopStatusTypeEnum::LOADING->value);
            $table->integer('count')->default(0);
            $table->integer('step')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_assignment_questions');
    }
};
