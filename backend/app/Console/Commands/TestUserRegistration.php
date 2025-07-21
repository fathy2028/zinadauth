<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestUserRegistration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-registration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user registration with BaseCrudController integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing User Registration with BaseCrudController');
        $this->info('=================================================');

        $baseUrl = 'http://localhost:8000/api';

        // Test data for different user types
        $testUsers = [
            [
                'name' => 'Test Participant User',
                'email' => 'testparticipant@example.com',
                'password' => 'password123',
                'user_name' => 'testparticipant',
                'type' => 'participant',
                'theme' => 'light',
            ],
            [
                'name' => 'Test Facilitator User',
                'email' => 'testfacilitator@example.com',
                'password' => 'password456',
                'user_name' => 'testfacilitator',
                'type' => 'facilitator',
                'theme' => 'dark',
            ],
            [
                'name' => 'Test Admin User',
                'email' => 'testadmin@example.com',
                'password' => 'password789',
                'user_name' => 'testadmin',
                'type' => 'admin',
                'theme' => 'light',
            ],
        ];

        foreach ($testUsers as $index => $userData) {
            $this->info("\n" . ($index + 1) . ". Testing registration for {$userData['type']} user:");
            $this->line("   Email: {$userData['email']}");

            try {
                $response = Http::post("{$baseUrl}/register", $userData);

                if ($response->successful()) {
                    $data = $response->json();
                    $this->line("   ✓ Registration successful");
                    $this->line("   ✓ User ID: " . $data['data']['user']['id']);
                    $this->line("   ✓ User Type: " . $data['data']['user']['type']);
                    
                    if (isset($data['data']['capabilities'])) {
                        $this->line("   ✓ Capabilities loaded");
                        $this->line("   ✓ Primary Role: " . $data['data']['capabilities']['primary_role']);
                        $this->line("   ✓ Is Admin: " . ($data['data']['capabilities']['is_admin'] ? 'Yes' : 'No'));
                        $this->line("   ✓ Is Facilitator: " . ($data['data']['capabilities']['is_facilitator'] ? 'Yes' : 'No'));
                        $this->line("   ✓ Is Participant: " . ($data['data']['capabilities']['is_participant'] ? 'Yes' : 'No'));
                    }
                } else {
                    $this->error("   ✗ Registration failed");
                    $this->error("   Status: " . $response->status());
                    $this->error("   Response: " . $response->body());
                }

            } catch (\Exception $e) {
                $this->error("   ✗ Exception occurred: " . $e->getMessage());
            }

            // Add delay to avoid rate limiting
            sleep(1);
        }

        // Test validation errors
        $this->info("\n4. Testing validation errors:");
        $invalidData = [
            'name' => 'T', // Too short
            'email' => 'invalid-email', // Invalid email
            'password' => '123', // Too weak
            'type' => 'invalid-type', // Invalid type
        ];

        try {
            $response = Http::post("{$baseUrl}/register", $invalidData);
            
            if ($response->status() === 422) {
                $this->line("   ✓ Validation errors handled correctly");
                $errors = $response->json()['errors'] ?? [];
                foreach ($errors as $field => $messages) {
                    $this->line("   ✓ {$field}: " . implode(', ', $messages));
                }
            } else {
                $this->error("   ✗ Expected validation error (422), got: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Exception during validation test: " . $e->getMessage());
        }

        // Test duplicate email
        $this->info("\n5. Testing duplicate email validation:");
        $duplicateData = [
            'name' => 'Duplicate User',
            'email' => 'testparticipant@example.com', // Already used above
            'password' => 'password999',
        ];

        try {
            $response = Http::post("{$baseUrl}/register", $duplicateData);
            
            if ($response->status() === 422) {
                $this->line("   ✓ Duplicate email validation working");
                $errors = $response->json()['errors'] ?? [];
                if (isset($errors['email'])) {
                    $this->line("   ✓ Email error: " . implode(', ', $errors['email']));
                }
            } else {
                $this->error("   ✗ Expected validation error for duplicate email");
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Exception during duplicate email test: " . $e->getMessage());
        }

        $this->info("\n=================================================");
        $this->info("User Registration Test Completed!");
        $this->info("Check the users table to verify the created users.");
    }
}
