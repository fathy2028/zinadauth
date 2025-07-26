<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Question;
use App\Models\User;
use App\Enums\QuestionTypeEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tymon\JWTAuth\Facades\JWTAuth;

class QuestionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $facilitator;
    protected User $participant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users with different roles
        $this->admin = User::factory()->create(['type' => UserTypeEnum::ADMIN->value]);
        $this->facilitator = User::factory()->create(['type' => UserTypeEnum::FACILITATOR->value]);
        $this->participant = User::factory()->create(['type' => UserTypeEnum::PARTICIPANT->value]);
    }

    /**
     * Authenticate a user using JWT
     */
    protected function actingAsJWT(User $user)
    {
        $token = JWTAuth::fromUser($user);
        $this->withHeaders(['Authorization' => 'Bearer ' . $token]);
        return $this;
    }

    /** @test */
    public function authenticated_users_can_view_questions_list()
    {
        $this->actingAsJWT($this->participant);

        Question::factory()->count(5)->create();

        $response = $this->getJson('/api/questions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'questions' => [
                            '*' => [
                                'id',
                                'question_text',
                                'type',
                                'points',
                                'duration',
                                'created_at'
                            ]
                        ],
                        'pagination' => [
                            'current_page',
                            'last_page',
                            'per_page',
                            'total'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_questions()
    {
        $response = $this->getJson('/api/questions');

        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_create_single_choice_question()
    {
        $this->actingAsJWT($this->admin);

        $questionData = [
            'question_text' => 'What is the capital of France?',
            'question_text_ar' => 'ما هي عاصمة فرنسا؟',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['London', 'Berlin', 'Paris', 'Madrid'],
            'choices_ar' => ['لندن', 'برلين', 'باريس', 'مدريد'],
            'answer' => [2],
            'points' => 10,
            'duration' => 30
        ];

        $response = $this->postJson('/api/questions', $questionData);

        $response->assertStatus(201)
                ->assertJsonFragment([
                    'status' => 'success',
                    'message' => 'Question created successfully'
                ]);

        $this->assertDatabaseHas('questions', [
            'question_text' => 'What is the capital of France?',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'created_by' => $this->admin->id
        ]);
    }

    /** @test */
    public function facilitator_can_create_questions()
    {
        $this->actingAsJWT($this->facilitator);

        $questionData = [
            'question_text' => 'Explain polymorphism in OOP.',
            'type' => QuestionTypeEnum::TEXT->value,
            'text_answer' => 'Polymorphism allows objects of different types to be treated as instances of the same type.',
            'points' => 20,
            'duration' => 120
        ];

        $response = $this->postJson('/api/questions', $questionData);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('questions', [
            'question_text' => 'Explain polymorphism in OOP.',
            'created_by' => $this->facilitator->id
        ]);
    }

    /** @test */
    public function participant_cannot_create_questions()
    {
        $this->actingAsJWT($this->participant);

        $questionData = [
            'question_text' => 'Test question',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['A', 'B', 'C'],
            'answer' => [0]
        ];

        $response = $this->postJson('/api/questions', $questionData);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_question_creation_data()
    {
        $this->actingAsJWT($this->facilitator);

        // Missing required fields
        $response = $this->postJson('/api/questions', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['question_text', 'type']);

        // Invalid question type
        $response = $this->postJson('/api/questions', [
            'question_text' => 'Valid question',
            'type' => 'invalid_type'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function authenticated_users_can_view_specific_question()
    {
        $this->actingAsJWT($this->participant);

        $question = Question::factory()->create();

        $response = $this->getJson("/api/questions/{$question->id}");

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'id' => $question->id,
                    'question_text' => $question->question_text
                ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_question()
    {
        $this->actingAsJWT($this->participant);

        $response = $this->getJson('/api/questions/non-existent-id');

        $response->assertStatus(404);
    }

    /** @test */
    public function facilitator_can_update_own_question()
    {
        $this->actingAsJWT($this->facilitator);

        $question = Question::factory()->create(['created_by' => $this->facilitator->id]);

        $updateData = [
            'question_text' => 'Updated question text',
            'points' => 15
        ];

        $response = $this->putJson("/api/questions/{$question->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'status' => 'success',
                    'message' => 'Question updated successfully'
                ]);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'question_text' => 'Updated question text',
            'points' => 15
        ]);
    }

    /** @test */
    public function facilitator_cannot_update_others_question()
    {
        $this->actingAsJWT($this->facilitator);

        $question = Question::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->putJson("/api/questions/{$question->id}", [
            'question_text' => 'Trying to update'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_any_question()
    {
        $this->actingAsJWT($this->admin);
        
        $question = Question::factory()->create(['created_by' => $this->facilitator->id]);

        $response = $this->putJson("/api/questions/{$question->id}", [
            'question_text' => 'Admin updated this'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'question_text' => 'Admin updated this'
        ]);
    }

    /** @test */
    public function facilitator_can_delete_own_question()
    {
        $this->actingAsJWT($this->facilitator);

        $question = Question::factory()->create(['created_by' => $this->facilitator->id]);

        $response = $this->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'status' => 'success',
                    'message' => 'Question deleted successfully'
                ]);

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    /** @test */
    public function facilitator_cannot_delete_others_question()
    {
        $this->actingAsJWT($this->facilitator);

        $question = Question::factory()->create(['created_by' => $this->admin->id]);

        $response = $this->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('questions', ['id' => $question->id]);
    }

    /** @test */
    public function admin_can_delete_any_question()
    {
        $this->actingAsJWT($this->admin);

        $question = Question::factory()->create(['created_by' => $this->facilitator->id]);

        $response = $this->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    /** @test */
    public function participant_cannot_delete_questions()
    {
        $this->actingAsJWT($this->participant);

        $question = Question::factory()->create();

        $response = $this->deleteJson("/api/questions/{$question->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_users_can_search_questions()
    {
        $this->actingAsJWT($this->participant);

        Question::factory()->create(['question_text' => 'PHP programming question']);
        Question::factory()->create(['question_text' => 'JavaScript coding challenge']);

        $response = $this->getJson('/api/questions-search?q=PHP');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['question_text' => 'PHP programming question']);
    }

    /** @test */
    public function users_can_filter_questions_by_type()
    {
        $this->actingAsJWT($this->participant);

        Question::factory()->create(['type' => QuestionTypeEnum::SINGLE_CHOICE]);
        Question::factory()->create(['type' => QuestionTypeEnum::MULTIPLE_CHOICE]);
        Question::factory()->create(['type' => QuestionTypeEnum::TEXT]);

        $response = $this->getJson('/api/questions/type/' . QuestionTypeEnum::SINGLE_CHOICE->value);

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function users_can_get_random_questions_by_type()
    {
        $this->actingAsJWT($this->participant);

        Question::factory()->count(10)->create(['type' => QuestionTypeEnum::SINGLE_CHOICE]);

        $response = $this->getJson('/api/questions/random/' . QuestionTypeEnum::SINGLE_CHOICE->value . '?count=3');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function only_admin_can_bulk_create_questions()
    {
        $this->actingAsJWT($this->admin);

        $questionsData = [
            'questions' => [
                [
                    'question_text' => 'Question 1',
                    'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
                    'choices' => ['A', 'B', 'C'],
                    'answer' => [0]
                ],
                [
                    'question_text' => 'Question 2',
                    'type' => QuestionTypeEnum::TEXT->value,
                    'text_answer' => 'Answer 2'
                ]
            ]
        ];

        $response = $this->postJson('/api/questions/bulk-create', $questionsData);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('questions', ['question_text' => 'Question 1']);
        $this->assertDatabaseHas('questions', ['question_text' => 'Question 2']);
    }

    /** @test */
    public function facilitator_cannot_bulk_create_questions()
    {
        $this->actingAsJWT($this->facilitator);

        $response = $this->postJson('/api/questions/bulk-create', [
            'questions' => [
                ['question_text' => 'Test', 'type' => 'single_choice']
            ]
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function only_admin_can_bulk_delete_questions()
    {
        $this->actingAsJWT($this->admin);

        $questions = Question::factory()->count(3)->create();
        $ids = $questions->pluck('id')->toArray();

        $response = $this->postJson('/api/questions/bulk-delete', ['ids' => $ids]);

        $response->assertStatus(200)
                ->assertJsonFragment(['deleted_count' => 3]);

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('questions', ['id' => $id]);
        }
    }

    /** @test */
    public function facilitator_cannot_bulk_delete_questions()
    {
        $this->actingAsJWT($this->facilitator);

        $questions = Question::factory()->count(2)->create();

        $response = $this->postJson('/api/questions/bulk-delete', [
            'ids' => $questions->pluck('id')->toArray()
        ]);

        $response->assertStatus(403);
    }
}
