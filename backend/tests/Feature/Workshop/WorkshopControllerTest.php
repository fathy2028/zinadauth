<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workshop;
use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class WorkshopControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function authenticated_users_can_view_workshops_list()
    {
        $this->actingAsJWT($this->participant);

        Workshop::factory()->count(5)->create();

        $response = $this->getJson('/api/workshops');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_workshops()
    {
        $response = $this->getJson('/api/workshops');

        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_create_workshop()
    {
        $this->actingAsJWT($this->admin);

        $workshopData = [
            'name' => 'Laravel Advanced Workshop',
            'description' => 'Deep dive into Laravel framework',
            'start_date' => '2024-12-01',
            'end_date' => '2024-12-03',
            'location' => 'Conference Room A',
            'max_participants' => 50
        ];

        $response = $this->postJson('/api/workshops', $workshopData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Record created successfully'
            ]);

        $this->assertDatabaseHas('workshops', [
            'name' => 'Laravel Advanced Workshop',
            'created_by' => $this->admin->id
        ]);
    }

    /** @test */
    public function facilitator_can_create_workshops()
    {
        $this->actingAsJWT($this->facilitator);

        $workshopData = [
            'name' => 'PHP Best Practices',
            'description' => 'Learn modern PHP development practices',
            'start_date' => '2024-12-15',
            'end_date' => '2024-12-16',
            'location' => 'Online',
            'max_participants' => 30
        ];

        $response = $this->postJson('/api/workshops', $workshopData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('workshops', [
            'name' => 'PHP Best Practices',
            'created_by' => $this->facilitator->id
        ]);
    }

    /** @test */
    public function participant_cannot_create_workshops()
    {
        $this->actingAsJWT($this->participant);

        $workshopData = [
            'name' => 'Unauthorized Workshop',
            'description' => 'This should not be created'
        ];

        $response = $this->postJson('/api/workshops', $workshopData);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_workshop_creation_data()
    {
        $this->actingAsJWT($this->facilitator);

        // Missing required fields
        $response = $this->postJson('/api/workshops', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Invalid date format
        $response = $this->postJson('/api/workshops', [
            'name' => 'Valid Workshop',
            'start_date' => 'invalid-date'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    /** @test */
    public function authenticated_users_can_view_specific_workshop()
    {
        $this->actingAsJWT($this->participant);

        $workshop = Workshop::factory()->create();

        $response = $this->getJson("/api/workshops/{$workshop->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Record found successfully'
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_workshop()
    {
        $this->actingAsJWT($this->participant);

        $response = $this->getJson('/api/workshops/non-existent-id');

        $response->assertStatus(404);
    }

    /** @test */
    public function facilitator_can_update_own_workshop()
    {
        $this->actingAsJWT($this->facilitator);

        $workshop = Workshop::factory()->create(['created_by' => $this->facilitator->id]);

        $updateData = [
            'name' => 'Updated Workshop Name',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/workshops/{$workshop->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Record updated successfully'
            ]);

        $this->assertDatabaseHas('workshops', [
            'id' => $workshop->id,
            'name' => 'Updated Workshop Name'
        ]);
    }

    /** @test */
    public function facilitator_cannot_update_others_workshop()
    {
        $this->actingAsJWT($this->facilitator);

        $workshop = Workshop::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->putJson("/api/workshops/{$workshop->id}", [
            'name' => 'Trying to update'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_any_workshop()
    {
        $this->actingAsJWT($this->admin);

        $workshop = Workshop::factory()->create(['created_by' => $this->facilitator->id]);

        $response = $this->putJson("/api/workshops/{$workshop->id}", [
            'name' => 'Admin updated this'
