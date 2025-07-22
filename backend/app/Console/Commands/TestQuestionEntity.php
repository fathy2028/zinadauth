<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Interfaces\QuestionRepositoryInterface;
use App\Enums\QuestionTypeEnum;
use App\Models\User;

class TestQuestionEntity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:question-entity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Question entity implementation';

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
        $this->info('Testing Question Entity Implementation');
        $this->info('=====================================');

        try {
            // Test 1: Create different types of questions
            $this->info('1. Testing question creation...');
            
            // Create a test user if needed
            $user = User::first();
            if (!$user) {
                $this->error('No users found. Please create a user first.');
                return;
            }

            // Single choice question
            $singleChoiceData = [
                'question_text' => 'What is the capital of France?',
                'question_text_ar' => 'ما هي عاصمة فرنسا؟',
                'choices' => ['London', 'Berlin', 'Paris', 'Madrid'],
                'choices_ar' => ['لندن', 'برلين', 'باريس', 'مدريد'],
                'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
                'answer' => [2], // Paris (stored as array)
                'points' => 10,
                'duration' => 30,
                'created_by' => $user->id,
            ];

            $singleChoiceQuestion = $this->questionRepository->createQuestion($singleChoiceData);
            $this->line("   ✓ Single choice question created: {$singleChoiceQuestion->id}");

            // Multiple choice question
            $multipleChoiceData = [
                'question_text' => 'Which of the following are programming languages?',
                'choices' => ['PHP', 'HTML', 'JavaScript', 'CSS', 'Python'],
                'type' => QuestionTypeEnum::MULTIPLE_CHOICE->value,
                'answer' => [0, 2, 4], // PHP, JavaScript, Python
                'points' => 15,
                'duration' => 45,
                'created_by' => $user->id,
            ];

            $multipleChoiceQuestion = $this->questionRepository->createQuestion($multipleChoiceData);
            $this->line("   ✓ Multiple choice question created: {$multipleChoiceQuestion->id}");

            // Text question
            $textQuestionData = [
                'question_text' => 'Explain the concept of Object-Oriented Programming.',
                'type' => QuestionTypeEnum::TEXT->value,
                'text_answer' => 'Object-Oriented Programming is a programming paradigm based on objects and classes.',
                'points' => 20,
                'duration' => 120,
                'created_by' => $user->id,
            ];

            $textQuestion = $this->questionRepository->createQuestion($textQuestionData);
            $this->line("   ✓ Text question created: {$textQuestion->id}");

            // Test 2: Test question methods
            $this->info('2. Testing question methods...');
            
            $this->line("   ✓ Single choice - Is multiple choice: " . ($singleChoiceQuestion->isMultipleChoice() ? 'Yes' : 'No'));
            $this->line("   ✓ Single choice - Is single choice: " . ($singleChoiceQuestion->isSingleChoice() ? 'Yes' : 'No'));
            $this->line("   ✓ Text question - Is text based: " . ($textQuestion->isTextBased() ? 'Yes' : 'No'));
            $this->line("   ✓ Single choice difficulty: " . $singleChoiceQuestion->getDifficulty());

            // Test 3: Test repository methods
            $this->info('3. Testing repository methods...');
            
            $singleChoiceQuestions = $this->questionRepository->getByType(QuestionTypeEnum::SINGLE_CHOICE);
            $this->line("   ✓ Single choice questions count: " . $singleChoiceQuestions->count());

            $userQuestions = $this->questionRepository->getByCreator($user->id);
            $this->line("   ✓ Questions by user: " . $userQuestions->count());

            $randomQuestions = $this->questionRepository->getRandomByType(QuestionTypeEnum::SINGLE_CHOICE, 2);
            $this->line("   ✓ Random single choice questions: " . $randomQuestions->count());

            // Test 4: Test search functionality
            $this->info('4. Testing search functionality...');
            
            $searchResults = $this->questionRepository->search('capital', 'en');
            $this->line("   ✓ Search results for 'capital': " . $searchResults->count());

            // Test 5: Test statistics
            $this->info('5. Testing statistics...');
            
            $stats = $this->questionRepository->getStatistics();
            $this->line("   ✓ Total questions: " . $stats['total_questions']);
            $this->line("   ✓ Average points: " . $stats['average_points']);
            $this->line("   ✓ Average duration: " . $stats['average_duration']);

            // Test 6: Test question validation
            $this->info('6. Testing answer validation...');
            
            $correctAnswer = $singleChoiceQuestion->isCorrectAnswer(2);
            $wrongAnswer = $singleChoiceQuestion->isCorrectAnswer(0);
            $this->line("   ✓ Correct answer validation: " . ($correctAnswer ? 'Pass' : 'Fail'));
            $this->line("   ✓ Wrong answer validation: " . ($wrongAnswer ? 'Fail' : 'Pass'));

            $textCorrect = $textQuestion->isCorrectAnswer('Object-Oriented Programming is a programming paradigm based on objects and classes.');
            $this->line("   ✓ Text answer validation: " . ($textCorrect ? 'Pass' : 'Fail'));

            // Test 7: Test duplication
            $this->info('7. Testing question duplication...');
            
            $duplicatedQuestion = $this->questionRepository->duplicate($singleChoiceQuestion->id);
            $this->line("   ✓ Question duplicated: {$duplicatedQuestion->id}");
            $this->line("   ✓ Original text: {$singleChoiceQuestion->question_text}");
            $this->line("   ✓ Duplicated text: {$duplicatedQuestion->question_text}");

            // Test 8: Test update
            $this->info('8. Testing question update...');
            
            $updateData = [
                'points' => 25,
                'duration' => 60,
            ];
            
            $updatedQuestion = $this->questionRepository->updateQuestion($singleChoiceQuestion->id, $updateData);
            $this->line("   ✓ Question updated - Points: {$updatedQuestion->points}, Duration: {$updatedQuestion->duration}");

            // Test 9: Test pagination
            $this->info('9. Testing pagination...');
            
            $paginatedQuestions = $this->questionRepository->getPaginated(['type' => QuestionTypeEnum::SINGLE_CHOICE->value], 2);
            $this->line("   ✓ Paginated questions - Total: {$paginatedQuestions->total()}, Per page: {$paginatedQuestions->perPage()}");

            // Cleanup
            $this->info('10. Cleaning up test data...');
            
            $this->questionRepository->delete($singleChoiceQuestion->id);
            $this->questionRepository->delete($multipleChoiceQuestion->id);
            $this->questionRepository->delete($textQuestion->id);
            $this->questionRepository->delete($duplicatedQuestion->id);
            $this->line("   ✓ Test questions deleted");

            $this->info('=====================================');
            $this->info('Question Entity Test Completed Successfully!');

        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
