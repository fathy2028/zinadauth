<?php

namespace Database\Factories;

use App\Enums\WorkshopStatusTypeEnum;
use App\Models\Workshop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkshopFactory extends Factory
{
    protected $model = Workshop::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_at' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'pin_code' => rand(100000, 999999),
            'qr_status' => rand(0, 1),
            'status' => WorkshopStatusTypeEnum::ACTIVE,
            'created_by' => User::factory(),
        ];
    }
}
