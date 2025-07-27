<?php

namespace Tests\Feature\Workshop;

use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\BaseTestClass;

class WorkshopControllerTest extends BaseTestClass
{
    use RefreshDatabase, WithFaker;

    public function test_authenticated_users_cannot_view_workshops_list()
    {
        $this->actingAsJWT($this->facilitator);

        Workshop::factory()->count(5)->create();

        $response = $this->getJson('/api/workshops');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'start_at',
                        'end_at',
                        'status',
                        'qr_status',
                        'pin_code',
                        'setting_id',
                        'created_by',
                        'created_at'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to'
                ]
            ]);
    }

    public function test_unauthenticated_users_cannot_access_workshops()
    {
        $response = $this->getJson('/api/workshops');

        $response->assertStatus(401);
    }

    public function test_admin_can_create_workshop()
    {
        $this->actingAsJWT($this->admin);

        $workshopData = [
            'title' => 'Admin Workshop',
            'description' => 'this is the admin workshop',
            'start_at' => now()->format('Y-m-d'),
            'end_at' => now()->addDays(3)->format('Y-m-d'),
            'pin_code' => '123455',
            'qr_status' => 0,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/workshops', $workshopData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Record created successfully'
            ]);

        $this->assertDatabaseHas('workshops', [
            'title' => 'Admin Workshop',
            'created_by' => $this->admin->id
        ]);
    }

    public function test_facilitator_can_create_workshops()
    {
        $this->actingAsJWT($this->facilitator);

        $workshopData = [
            'title' => 'Test Workshop',
            'description' => 'Test Workshop',
            'start_at' => now()->format('Y-m-d'),
            'end_at' => now()->addDays(3)->format('Y-m-d'),
            'pin_code' => '123456',
            'qr_status' => 0,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/workshops', $workshopData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('workshops', [
            'title' => 'Test Workshop',
            'created_by' => $this->facilitator->id
        ]);
    }

    public function test_participant_cannot_create_workshops()
    {
        $this->actingAsJWT($this->participant);

        $workshopData = [
            'title' => 'Unauthorized Workshop',
            'description' => 'This should not be created'
        ];

        $response = $this->postJson('/api/workshops', $workshopData);

        $response->assertStatus(403);
    }

    public function test_validates_workshop_creation_data()
    {
        $this->actingAsJWT($this->facilitator);

        // Missing required fields
        $response = $this->postJson('/api/workshops', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        // Invalid date format
        $response = $this->postJson('/api/workshops', [
            'title' => 'Valid Workshop',
            'start_at' => 'invalid-date'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_at']);
    }

    public function test_authenticated_users_can_view_specific_workshop()
    {
        $this->actingAsJWT($this->facilitator);

        $workshop = Workshop::factory()->create(['created_by' => $this->facilitator->id]);

        $response = $this->getJson("/api/workshops/{$workshop->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Record found successfully'
            ]);
    }

    public function test_returns_404_for_non_existent_workshop()
    {
        $this->actingAsJWT($this->participant);

        $response = $this->getJson('/api/workshops/non-existent-id');

        $response->assertStatus(404);
    }

    public function test_facilitator_can_update_own_workshop()
    {
        $this->actingAsJWT($this->facilitator);

        $workshop = Workshop::factory()->create(['created_by' => $this->facilitator->id]);

        $updateData = [
            'title' => 'Updated Workshop Name',
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
            'title' => 'Updated Workshop Name'
        ]);
    }

    public function test_facilitator_cannot_update_others_workshop()
    {
        $this->actingAsJWT($this->facilitator);

        $workshop = Workshop::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->putJson("/api/workshops/{$workshop->id}", [
            'title' => 'Trying to update'
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_workshop()
    {
        $this->actingAsJWT($this->admin);

        $workshop = Workshop::factory()->create(['created_by' => $this->facilitator->id]);

        $response = $this->putJson("/api/workshops/{$workshop->id}", [
            'title' => 'Admin updated this'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('workshops', [
            'id' => $workshop->id,
            'title' => 'Admin updated this'
        ]);
    }

    public function test_facilitator_can_delete_own_workshop()
    {
        $this->actingAsJWT($this->facilitator);

        $workshop = Workshop::factory()->create(['created_by' => $this->facilitator->id]);

        $response = $this->deleteJson("/api/workshops/{$workshop->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Record deleted successfully'
            ]);

        $this->assertDatabaseMissing('workshops', ['id' => $workshop->id]);
    }

    public function test_facilitator_cannot_delete_others_workshop()
    {
        $this->actingAsJWT($this->facilitator);

        $workshop = Workshop::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->deleteJson("/api/workshops/{$workshop->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('workshops', ['id' => $workshop->id]);
    }

    public function test_admin_can_delete_any_workshop()
    {
        $this->actingAsJWT($this->admin);

        $workshop = Workshop::factory()->create(['created_by' => $this->facilitator->id]);

        $response = $this->deleteJson("/api/workshops/{$workshop->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('workshops', ['id' => $workshop->id]);
    }

    public function test_participant_cannot_delete_workshops()
    {
        $this->actingAsJWT($this->participant);

        $workshop = Workshop::factory()->create();

        $response = $this->deleteJson("/api/workshops/{$workshop->id}");

        $response->assertStatus(403);
    }
    public function test_users_can_paginate_workshops()
    {
        $this->actingAsJWT($this->participant);

        Workshop::factory()->count(20)->create();

        $response = $this->getJson('/api/workshops?limit=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJson([
                'pagination' => [
                    'per_page' => 5,
                    'total' => 20
                ]
            ]);
    }

    public function test_respects_pagination_limits()
    {
        $this->actingAsJWT($this->participant);

        Workshop::factory()->count(150)->create();

        $response = $this->getJson('/api/workshops?limit=200'); // Exceeds max limit

        $response->assertStatus(200);

        $pagination = $response->json('pagination');
        $this->assertLessThanOrEqual(100, $pagination['per_page']); // Max limit is 100
    }
}
