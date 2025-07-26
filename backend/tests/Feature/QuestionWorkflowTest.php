<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Question;
use App\Models\User;
use App\Models\Assignment;
use App\Enums\QuestionTypeEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class QuestionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $facilitator;
    protected User $participant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['type' => UserTypeEnum::ADMIN->value]);
        $this->facilitator = User::factory()->create(['type' => UserTypeEnum::FACILITATOR->value]);
        $this->participant = User::factory()->create(['type' => UserTypeEnum::PARTICIPANT->value]);
    }

    /**
     * Helper method to authenticate user with JWT
     */
    protected function actingAsJWT(User $user)
    {
        // Clear any existing authentication
        try {
            $existingToken = JWTAuth::getToken();
            if ($existingToken) {
                JWTAuth::invalidate($existingToken);
            }
        } catch (\Exception) {
            // Token doesn't exist, continue
        }

        auth()->logout();

        // Set new authentication
        $token = JWTAuth::fromUser($user);
        $this->withHeaders(['Authorization' => 'Bearer ' . $token]);

        // Also set the user in the auth context for policies
        auth()->setUser($user);

        return $this;
    }

    /** @test */
    public function complete_question_management_workflow_for_facilitator()
    {
        $this->actingAsJWT($this->facilitator);

        // 1. Create a single choice question
        $singleChoiceData = [
            'question_text' => 'What is the capital of France?',
            'question_text_ar' => 'ما هي عاصمة فرنسا؟',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['London', 'Berlin', 'Paris', 'Madrid'],
            'choices_ar' => ['لندن', 'برلين', 'باريس', 'مدريد'],
            'answer' => [2],
            'points' => 10,
            'duration' => 30
        ];

        $response = $this->postJson('/api/questions', $singleChoiceData);
        $response->assertStatus(201);
        $singleChoiceQuestion = Question::where('question_text', 'What is the capital of France?')->first();

        // 2. Create a multiple choice question
        $multipleChoiceData = [
            'question_text' => 'Which are programming languages?',
            'type' => QuestionTypeEnum::MULTIPLE_CHOICE->value,
            'choices' => ['PHP', 'HTML', 'JavaScript', 'CSS'],
            'answer' => [0, 2],
            'points' => 15,
            'duration' => 45
        ];

        $response = $this->postJson('/api/questions', $multipleChoiceData);
        $response->assertStatus(201);
        $multipleChoiceQuestion = Question::where('question_text', 'Which are programming languages?')->first();

        // 3. Create a text question
        $textQuestionData = [
            'question_text' => 'Explain the concept of polymorphism in OOP.',
            'type' => QuestionTypeEnum::TEXT->value,
            'text_answer' => 'Polymorphism allows objects of different types to be treated as instances of the same type.',
            'points' => 20,
            'duration' => 120
        ];

        $response = $this->postJson('/api/questions', $textQuestionData);
        $response->assertStatus(201);
        $textQuestion = Question::where('question_text', 'Explain the concept of polymorphism in OOP.')->first();

        // 4. View all questions
        $response = $this->getJson('/api/questions');
        $response->assertStatus(200)
                ->assertJsonCount(3, 'data.questions');

        // 5. Search for specific questions
        $response = $this->getJson('/api/questions-search?q=France');
        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment(['question_text' => 'What is the capital of France?']);

        // 6. Filter by question type
        $response = $this->getJson('/api/questions/type/' . QuestionTypeEnum::SINGLE_CHOICE->value);
        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');

        // 7. Update a question
        $updateData = [
            'question_text' => 'What is the capital city of France?',
            'points' => 12
        ];

        $response = $this->putJson("/api/questions/{$singleChoiceQuestion->id}", $updateData);
        $response->assertStatus(200);

        $this->assertDatabaseHas('questions', [
            'id' => $singleChoiceQuestion->id,
            'question_text' => 'What is the capital city of France?',
            'points' => 12
        ]);

        // 8. Duplicate a question
        $response = $this->postJson("/api/questions/{$textQuestion->id}/duplicate");
        $response->assertStatus(201);

        $this->assertEquals(4, Question::count());

        // 9. View question statistics
        $response = $this->getJson('/api/questions-statistics');
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'total_questions',
                        'by_type',
                        'average_points',
                        'average_duration'
                    ]
                ]);

        // 10. Delete a question
        $response = $this->deleteJson("/api/questions/{$multipleChoiceQuestion->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('questions', ['id' => $multipleChoiceQuestion->id]);
        $this->assertEquals(3, Question::count());
    }

    /** @test */
    public function admin_can_perform_bulk_operations()
    {
        $this->actingAsJWT($this->admin);

        // 1. Bulk create questions
        $bulkData = [
            'questions' => [
                [
                    'question_text' => 'Bulk Question 1',
                    'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
                    'choices' => ['A', 'B', 'C', 'D'],
                    'answer' => [0],
                    'points' => 10
                ],
                [
                    'question_text' => 'Bulk Question 2',
                    'type' => QuestionTypeEnum::TEXT->value,
                    'text_answer' => 'Sample answer',
                    'points' => 15
                ],
                [
                    'question_text' => 'Bulk Question 3',
                    'type' => QuestionTypeEnum::MULTIPLE_CHOICE->value,
                    'choices' => ['Option 1', 'Option 2', 'Option 3'],
                    'answer' => [0, 2],
                    'points' => 20
                ]
            ]
        ];

        $response = $this->postJson('/api/questions/bulk-create', $bulkData);
        $response->assertStatus(201)
                ->assertJsonFragment(['created_count' => 3]);

        $this->assertEquals(3, Question::count());

        // 2. Verify all questions were created
        $this->assertDatabaseHas('questions', ['question_text' => 'Bulk Question 1']);
        $this->assertDatabaseHas('questions', ['question_text' => 'Bulk Question 2']);
        $this->assertDatabaseHas('questions', ['question_text' => 'Bulk Question 3']);

        // 3. Get all question IDs for bulk delete
        $questionIds = Question::pluck('id')->toArray();

        // 4. Bulk delete questions
        $response = $this->postJson('/api/questions/bulk-delete', ['ids' => $questionIds]);
        $response->assertStatus(200)
                ->assertJsonFragment(['deleted_count' => 3]);

        $this->assertEquals(0, Question::count());
    }

    /** @test */
    public function multilingual_question_workflow()
    {
        $this->actingAsJWT($this->facilitator);

        // Create a multilingual question
        $multilingualData = [
            'question_text' => 'What is object-oriented programming?',
            'question_text_ar' => 'ما هي البرمجة الكائنية؟',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['A programming paradigm', 'A database type', 'A web framework', 'An operating system'],
            'choices_ar' => ['نموذج برمجة', 'نوع قاعدة بيانات', 'إطار عمل ويب', 'نظام تشغيل'],
            'answer' => [0],
            'points' => 15,
            'duration' => 60
        ];

        $response = $this->postJson('/api/questions', $multilingualData);
        $response->assertStatus(201);

        $question = Question::where('question_text', 'What is object-oriented programming?')->first();

        // Test multilingual methods
        $this->assertEquals('What is object-oriented programming?', $question->getQuestionText('en'));
        $this->assertEquals('ما هي البرمجة الكائنية؟', $question->getQuestionText('ar'));

        $this->assertEquals(['A programming paradigm', 'A database type', 'A web framework', 'An operating system'], $question->getChoices('en'));
        $this->assertEquals(['نموذج برمجة', 'نوع قاعدة بيانات', 'إطار عمل ويب', 'نظام تشغيل'], $question->getChoices('ar'));
    }

    /** @test */
    public function question_assignment_integration()
    {
        $this->actingAsJWT($this->facilitator);

        // Create questions
        $questions = Question::factory()->count(3)->create(['created_by' => $this->facilitator->id]);
        
        // Create an assignment
        $assignment = Assignment::factory()->create(['created_by' => $this->facilitator->id]);

        // Attach questions to assignment
        foreach ($questions as $index => $question) {
            $assignment->questions()->attach($question->id, ['question_order' => $index + 1]);
        }

        // Verify relationships
        $this->assertCount(3, $assignment->questions);
        
        foreach ($questions as $question) {
            $this->assertCount(1, $question->assignments);
        }
    }

    /** @test */
    public function question_difficulty_calculation_workflow()
    {
        $this->actingAsJWT($this->facilitator);

        // Create questions with different difficulties
        $easyQuestion = Question::factory()->create([
            'points' => 5,
            'duration' => 300,
            'created_by' => $this->facilitator->id
        ]);

        $mediumQuestion = Question::factory()->create([
            'points' => 25,
            'duration' => 150,
            'created_by' => $this->facilitator->id
        ]);

        $hardQuestion = Question::factory()->create([
            'points' => 45,
            'duration' => 60,
            'created_by' => $this->facilitator->id
        ]);

        // Test difficulty calculation
        $this->assertEquals('easy', $easyQuestion->getDifficulty());
        $this->assertEquals('medium', $mediumQuestion->getDifficulty());
        $this->assertEquals('hard', $hardQuestion->getDifficulty());

        // Test filtering by difficulty through API
        $response = $this->getJson('/api/questions?min_points=20&max_points=30');
        $response->assertStatus(200);

        // Check if we have any data
        $responseData = $response->json();
        if (empty($responseData['data']['questions'])) {
            $this->fail('No questions found in response: ' . json_encode($responseData));
        }

        $response->assertJsonPath('data.questions.0.points', 25);

        $response = $this->getJson('/api/questions?min_duration=50&max_duration=100');
        $response->assertStatus(200)
                ->assertJsonPath('data.questions.0.duration', 60);
    }

    /** @test */
    public function authorization_workflow_across_different_user_types()
    {
        // Create questions by different users
        $adminQuestion = Question::factory()->create(['created_by' => $this->admin->id]);
        $facilitatorQuestion = Question::factory()->create(['created_by' => $this->facilitator->id]);

        // Test participant permissions
        $this->actingAsJWT($this->participant);
        
        // Can view questions
        $response = $this->getJson('/api/questions');
        $response->assertStatus(200);

        // Cannot create questions
        $response = $this->postJson('/api/questions', [
            'question_text' => 'Participant question',
            'type' => QuestionTypeEnum::TEXT->value
        ]);
        $response->assertStatus(403);

        // Cannot update questions
        $response = $this->putJson("/api/questions/{$facilitatorQuestion->id}", [
            'question_text' => 'Updated by participant'
        ]);
        $response->assertStatus(403);

        // Cannot delete questions
        $response = $this->deleteJson("/api/questions/{$facilitatorQuestion->id}");
        $response->assertStatus(403);

        // Test facilitator permissions
        $this->actingAsJWT($this->facilitator);

        // Can update own questions
        $response = $this->putJson("/api/questions/{$facilitatorQuestion->id}", [
            'question_text' => 'Updated by owner'
        ]);
        if ($response->status() !== 200) {
            dump('Response status: ' . $response->status());
            dump('Response content: ' . $response->content());
            dump('Facilitator ID: ' . $this->facilitator->id);
            dump('Question created_by: ' . $facilitatorQuestion->created_by);
        }
        $response->assertStatus(200);

        // Cannot update others' questions
        $response = $this->putJson("/api/questions/{$adminQuestion->id}", [
            'question_text' => 'Trying to update admin question'
        ]);
        $response->assertStatus(403);

        // Test admin permissions
        $this->actingAsJWT($this->admin);

        // Can update any question
        $response = $this->putJson("/api/questions/{$facilitatorQuestion->id}", [
            'question_text' => 'Updated by admin'
        ]);
        $response->assertStatus(200);

        // Can perform bulk operations
        $response = $this->postJson('/api/questions/bulk-delete', [
            'ids' => [$adminQuestion->id, $facilitatorQuestion->id]
        ]);
        $response->assertStatus(200);
    }
}
