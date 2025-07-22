<?php

namespace Tests\Feature\Authentication;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_minimum_required_fields(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'user_name',
                        'type',
                        'theme',
                        'created_at',
                        'updated_at',
                    ],
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'type' => 'participant', // Default value
            'theme' => 'light', // Default value
        ]);
    }

    public function test_user_can_register_with_all_fields(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'user_name' => 'jane_doe',
            'type' => 'facilitator',
            'theme' => 'dark',
            'web_engine' => 'chrome',
            'image' => 'base64_encoded_string',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
            'user_name' => 'jane_doe',
            'type' => 'facilitator',
            'theme' => 'dark',
            'web_engine' => 'chrome',
        ]);
    }

    public function test_user_cannot_register_with_invalid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'J', // Too short
            'email' => 'not-an-email',
            'password' => '123', // Too short
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => [
                    'name',
                    'email',
                    'password'
                ]
            ]);
    }

    public function test_email_must_be_unique(): void
    {
        // Create a user first
        User::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        // Try to register with the same email
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email', function ($errors) {
                return count(array_filter($errors, fn($error) => str_contains($error, 'already registered')
                    )) > 0;
            });
    }

    public function test_username_must_be_unique(): void
    {
        // Create a user first
        User::factory()->create([
            'user_name' => 'testuser',
        ]);

        // Try to register with the same username
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'user_name' => 'testuser',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.user_name', function ($errors) {
                return count(array_filter($errors, fn($error) => str_contains($error, 'already taken')
                    )) > 0;
            });
    }

    public function test_data_is_normalized_before_validation(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '  John Doe  ', // Extra spaces
            'email' => '  JOHN@EXAMPLE.COM  ', // Uppercase and spaces
            'password' => 'password123',
            'user_name' => '  TestUser  ', // Extra spaces and mixed case
            'type' => '  FACILITATOR  ', // Extra spaces and uppercase
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'user_name' => 'testuser',
            'type' => 'facilitator',
        ]);
    }
}
