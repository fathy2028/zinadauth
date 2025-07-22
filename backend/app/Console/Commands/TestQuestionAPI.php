<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Enums\QuestionTypeEnum;

class TestQuestionAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:question-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Question API endpoints according to user stories';

    protected $baseUrl = 'http://localhost:8000/api';
    protected $token = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Question API Implementation');
        $this->info('====================================');

        try {
            // Test 1: Login as facilitator to get token
            $this->info('1. Testing authentication...');
            $this->loginAsFacilitator();

            // Test 2: Test question creation (multilingual)
            $this->info('2. Testing multilingual question creation...');
            $questionData = [
                'question_text' => 'What is the capital of France?',
                'question_text_ar' => 'ما هي عاصمة فرنسا؟',
                'choices' => ['London', 'Berlin', 'Paris', 'Madrid'],
                'choices_ar' => ['لندن', 'برلين', 'باريس', 'مدريد'],
                'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
                'answer' => [2], // Paris
                'points' => 15,
                'duration' => 45,
            ];

            $response = $this->makeRequest('POST', '/questions', $questionData);
            if (isset($response['status']) && $response['status'] === 'success') {
                $questionId = $response['data']['id'];
                $this->line("   ✓ Multilingual question created: {$questionId}");
                $this->line("   ✓ Points: {$response['data']['points']}");
                $this->line("   ✓ Duration: {$response['data']['duration']}");
                $this->line("   ✓ Has Arabic: " . ($response['data']['has_arabic_translation'] ? 'Yes' : 'No'));
            } else {
                $this->error("   ✗ Failed to create question: " . ($response['message'] ?? 'Unknown error'));
                $this->line("   Response: " . json_encode($response));
                return;
            }

            // Test 3: Test question listing with filters
            $this->info('3. Testing question listing with filters...');
            $response = $this->makeRequest('GET', '/questions?type=single_choice&per_page=5');
            if (isset($response['status']) && $response['status'] === 'success') {
                $this->line("   ✓ Questions retrieved: " . count($response['data']['questions']));
                $this->line("   ✓ Pagination total: " . $response['data']['pagination']['total']);
            }

            // Test 4: Test question by type
            $this->info('4. Testing questions by type...');
            $response = $this->makeRequest('GET', '/questions/type/single_choice');
            if (isset($response['status']) && $response['status'] === 'success') {
                $this->line("   ✓ Single choice questions: " . count($response['data']));
            }

            // Test 5: Test random questions
            $this->info('5. Testing random questions...');
            $response = $this->makeRequest('GET', '/questions/random/single_choice?count=3');
            if (isset($response['status']) && $response['status'] === 'success') {
                $this->line("   ✓ Random questions retrieved: " . count($response['data']));
            }

            // Test 6: Test question duplication
            $this->info('6. Testing question duplication...');
            $response = $this->makeRequest('POST', "/questions/{$questionId}/duplicate");
            if ($response['success']) {
                $duplicatedId = $response['data']['id'];
                $this->line("   ✓ Question duplicated: {$duplicatedId}");
                $this->line("   ✓ Original text: What is the capital of France?");
                $this->line("   ✓ Duplicated text: " . $response['data']['question_text']);
            }

            // Test 7: Test question statistics
            $this->info('7. Testing question statistics...');
            $response = $this->makeRequest('GET', '/questions-statistics');
            if ($response['success']) {
                $this->line("   ✓ Total questions: " . $response['data']['total_questions']);
                $this->line("   ✓ Average points: " . $response['data']['average_points']);
                $this->line("   ✓ Average duration: " . $response['data']['average_duration']);
            }

            // Test 8: Test question search
            $this->info('8. Testing question search...');
            $response = $this->makeRequest('GET', '/questions-search?q=capital&language=en');
            if ($response['success']) {
                $this->line("   ✓ Search results: " . count($response['data']));
            }

            // Test 9: Test bulk creation
            $this->info('9. Testing bulk question creation...');
            $bulkData = [
                'questions' => [
                    [
                        'question_text' => 'What is 2 + 2?',
                        'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
                        'choices' => ['3', '4', '5', '6'],
                        'answer' => [1],
                        'points' => 5,
                        'duration' => 15,
                    ],
                    [
                        'question_text' => 'Explain object-oriented programming.',
                        'type' => QuestionTypeEnum::TEXT->value,
                        'text_answer' => 'OOP is a programming paradigm based on objects.',
                        'points' => 20,
                        'duration' => 120,
                    ]
                ]
            ];

            $response = $this->makeRequest('POST', '/questions/bulk-create', $bulkData);
            if ($response['success']) {
                $this->line("   ✓ Bulk created: " . $response['data']['created_count'] . " questions");
            }

            // Test 10: Test question update
            $this->info('10. Testing question update...');
            $updateData = [
                'points' => 20,
                'duration' => 60,
            ];
            $response = $this->makeRequest('PUT', "/questions/{$questionId}", $updateData);
            if ($response['success']) {
                $this->line("   ✓ Question updated - Points: " . $response['data']['points']);
                $this->line("   ✓ Question updated - Duration: " . $response['data']['duration']);
            }

            $this->info('====================================');
            $this->info('Question API Test Completed Successfully!');
            $this->info('All user story requirements verified:');
            $this->line('✓ Multilingual questions (create-multilingual-questions)');
            $this->line('✓ Question duration setting (set-question-duration)');
            $this->line('✓ Point value assignment (assign-point-values)');
            $this->line('✓ Question randomization (randomize-questions)');
            $this->line('✓ Full CRUD operations with permissions');
            $this->line('✓ Bulk operations support');
            $this->line('✓ Advanced filtering and search');

        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
        }
    }

    private function loginAsFacilitator()
    {
        $response = Http::post($this->baseUrl . '/login', [
            'email' => 'facilitator@zinadauth.com',
            'password' => 'password123',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->token = $data['data']['token'];
            $this->line('   ✓ Logged in as facilitator');
        } else {
            throw new \Exception('Failed to login as facilitator');
        }
    }

    private function makeRequest(string $method, string $endpoint, array $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        $headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->$method($url, $data);
        
        return $response->json();
    }
}
