<?php

namespace Database\Factories;

use App\Enums\AssignmentQuestionOrderEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence,
            'description' => fake()->paragraph,
            'question_order' => fake()->randomElement(AssignmentQuestionOrderEnum::values()),
            'created_by' => User::factory(),
        ];
    }
}
