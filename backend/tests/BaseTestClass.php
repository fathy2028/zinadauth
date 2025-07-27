<?php
namespace Tests;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseTestClass extends TestCase
{
    protected User $admin;
    protected User $facilitator;
    protected User $participant;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed Roles:
        $this->seed(RolePermissionSeeder::class);


        // Create users with different roles
        $this->admin = User::factory()->create(['type' => UserTypeEnum::ADMIN->value])
            ->assignRole(UserTypeEnum::ADMIN);
        $this->facilitator = User::factory()->create(['type' => UserTypeEnum::FACILITATOR->value])
            ->assignRole(UserTypeEnum::FACILITATOR);
        $this->participant = User::factory()->create(['type' => UserTypeEnum::PARTICIPANT->value])
            ->assignRole(UserTypeEnum::PARTICIPANT);
    }

    protected function actingAsJWT(User $user)
    {
        $token = JWTAuth::fromUser($user);
        $this->withHeaders(['Authorization' => 'Bearer ' . $token]);
        return $this;
    }
}
