<?php

use App\Enums\AssignmentQuestionOrderEnum;
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
        Schema::create('assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->enum('question_order', AssignmentQuestionOrderEnum::values())->nullable()->default(AssignmentQuestionOrderEnum::ORDERED->value);
            $table->char('created_by', 36)->nullable()->index('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
