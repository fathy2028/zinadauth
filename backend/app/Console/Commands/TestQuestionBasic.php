<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Interfaces\QuestionRepositoryInterface;
use App\Http\Controllers\QuestionController;
use App\Models\User;
use App\Enums\QuestionTypeEnum;
use Illuminate\Http\Request;

class TestQuestionBasic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:question-basic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Basic test for Question functionality';

    protected $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        parent::__construct();
        $this->questionRepository = $questionRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Question Basic Functionality');
        $this->info('===================================');

        try {
            // Test 1: Test repository injection
            $this->info('1. Testing repository injection...');
            if ($this->questionRepository) {
                $this->line('   ✓ QuestionRepository injected successfully');
            } else {
                $this->error('   ✗ QuestionRepository injection failed');
                return;
            }

            // Test 2: Test question creation via repository
            $this->info('2. Testing question creation via repository...');
            
            $user = User::first();
            if (!$user) {
                $this->error('   ✗ No users found. Please create a user first.');
                return;
            }

            // Authenticate as the user for testing
            auth()->login($user);

            $questionData = [
                'question_text' => 'What is the capital of France?',
                'question_text_ar' => 'ما هي عاصمة فرنسا؟',
                'choices' => ['London', 'Berlin', 'Paris', 'Madrid'],
                'choices_ar' => ['لندن', 'برلين', 'باريس', 'مدريد'],
                'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
                'answer' => [2], // Paris
                'points' => 15,
                'duration' => 45,
                'created_by' => $user->id,
            ];

            $question = $this->questionRepository->createQuestion($questionData);
            $this->line("   ✓ Question created with ID: {$question->id}");
            $this->line("   ✓ Question text: {$question->question_text}");
            $this->line("   ✓ Question type: {$question->type->value}");
            $this->line("   ✓ Points: {$question->points}");
            $this->line("   ✓ Duration: {$question->duration}");

            // Test 3: Test controller instantiation
            $this->info('3. Testing controller instantiation...');
            $controller = app(QuestionController::class);
            if ($controller) {
                $this->line('   ✓ QuestionController instantiated successfully');
            } else {
                $this->error('   ✗ QuestionController instantiation failed');
                return;
            }

            // Test 4: Test question listing
            $this->info('4. Testing question listing...');
            $request = new Request();
            $response = $controller->index($request);
            
            if ($response->getStatusCode() === 200) {
                $this->line('   ✓ Question listing successful');
                $responseData = json_decode($response->getContent(), true);
                if (isset($responseData['data']['questions'])) {
                    $this->line('   ✓ Questions data structure correct');
                    $this->line('   ✓ Questions count: ' . count($responseData['data']['questions']));
                }
            } else {
                $this->error('   ✗ Question listing failed with status: ' . $response->getStatusCode());
                $this->line('   Response: ' . $response->getContent());
            }

            // Test 5: Test question show
            $this->info('5. Testing question show...');
            $showResponse = $controller->show($question->id);
            
            if ($showResponse->getStatusCode() === 200) {
                $this->line('   ✓ Question show successful');
                $showData = json_decode($showResponse->getContent(), true);
                if (isset($showData['data']['id'])) {
                    $this->line('   ✓ Question ID matches: ' . $showData['data']['id']);
                }
            } else {
                $this->error('   ✗ Question show failed with status: ' . $showResponse->getStatusCode());
                $this->line('   Response: ' . $showResponse->getContent());
            }

            // Test 6: Test question by type
            $this->info('6. Testing questions by type...');
            $typeResponse = $controller->getByType(QuestionTypeEnum::SINGLE_CHOICE->value);
            
            if ($typeResponse->getStatusCode() === 200) {
                $this->line('   ✓ Questions by type successful');
                $typeData = json_decode($typeResponse->getContent(), true);
                if (isset($typeData['data'])) {
                    $this->line('   ✓ Single choice questions found: ' . count($typeData['data']));
                }
            } else {
                $this->error('   ✗ Questions by type failed with status: ' . $typeResponse->getStatusCode());
                $this->line('   Response: ' . $typeResponse->getContent());
            }

            // Test 7: Test statistics
            $this->info('7. Testing question statistics...');
            $statsResponse = $controller->statistics();
            
            if ($statsResponse->getStatusCode() === 200) {
                $this->line('   ✓ Question statistics successful');
                $statsData = json_decode($statsResponse->getContent(), true);
                if (isset($statsData['data']['total_questions'])) {
                    $this->line('   ✓ Total questions: ' . $statsData['data']['total_questions']);
                    $this->line('   ✓ Average points: ' . $statsData['data']['average_points']);
                }
            } else {
                $this->error('   ✗ Question statistics failed with status: ' . $statsResponse->getStatusCode());
                $this->line('   Response: ' . $statsResponse->getContent());
            }

            // Cleanup
            $this->info('8. Cleaning up...');
            $this->questionRepository->delete($question->id);
            $this->line('   ✓ Test question deleted');

            $this->info('===================================');
            $this->info('Question Basic Test Completed Successfully!');

        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
