<?php

namespace Feature\Authentication;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = Auth::guard('api')->attempt(['email' => $user->email, 'password' => 'password']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User logged out successfully',
                'data' => null
            ]);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->deleteJson('/api/logout');

        $response->assertStatus(401);
    }
}
