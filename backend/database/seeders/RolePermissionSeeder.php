<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Enums\UserTypeEnum;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions based on the workshop system requirements
        $permissions = [
            // User Management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-user-roles',
            'suspend-users',
            
            // Workshop Management
            'view-workshops',
            'create-workshops',
            'edit-workshops',
            'delete-workshops',
            'manage-workshop-settings',
            'generate-qr-codes',
            'generate-pin-codes',
            'configure-workshop-branding',
            'view-workshop-analytics',
            
            // Assignment Management
            'view-assignments',
            'create-assignments',
            'edit-assignments',
            'delete-assignments',
            'deploy-assignments',
            'randomize-questions',
            'manage-assignment-templates',
            
            // Question Management
            'view-questions',
            'create-questions',
            'edit-questions',
            'delete-questions',
            'create-multilingual-questions',
            'set-question-duration',
            'assign-point-values',
            
            // Content Management
            'view-contents',
            'create-contents',
            'edit-contents',
            'delete-contents',
            'create-templates',
            'manage-powerpoint-integration',
            'create-multilingual-content',
            
            // Attendance & Participation
            'view-attendance',
            'track-attendance',
            'confirm-attendance-pin',
            'monitor-participation',
            'view-real-time-responses',
            
            // Progress & Analytics
            'view-progress',
            'view-individual-progress',
            'view-leaderboard',
            'view-dashboard-analytics',
            'view-workshop-summary',
            'compare-workshop-performance',
            'export-analytics',
            
            // Assessment & Grading
            'submit-responses',
            'view-responses',
            'grade-responses',
            'provide-feedback',
            'view-assessment-results',
            
            // System Configuration
            'manage-system-settings',
            'configure-branding',
            'set-global-timing',
            'manage-language-settings',
            'configure-domain-settings',
            
            // Administrative Functions
            'view-system-statistics',
            'manage-departments',
            'assign-administrative-roles',
            'suspend-administrative-access',
            'view-audit-logs',
            
            // Real-time Collaboration
            'activate-real-time-questions',
            'monitor-live-sessions',
            'adjust-content-delivery',
            'manage-synchronized-sessions',
            
            // Multi-device Support
            'access-mobile-features',
            'sync-across-devices',
            'use-qr-code-joining',
            
            // Integration & API
            'access-api',
            'manage-integrations',
            'export-data',
            'import-data',
        ];

        // Create permissions (only if they don't exist)
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles without permissions (permissions will be assigned later)
        $this->createAdminRole();
        $this->createFacilitatorRole();
        $this->createParticipantRole();
        
        // Create default users
        $this->createDefaultUsers();
    }

    private function createAdminRole()
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        // Permissions will be assigned later
    }

    private function createFacilitatorRole()
    {
        $role = Role::firstOrCreate(['name' => 'facilitator']);
        // Permissions will be assigned later
    }

    private function createParticipantRole()
    {
        $role = Role::firstOrCreate(['name' => 'participant']);
        // Permissions will be assigned later
    }

    private function createDefaultUsers()
    {
        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@zinadauth.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'user_name' => 'admin',
                'type' => UserTypeEnum::ADMIN->value,
                'is_active' => true,
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create Facilitator
        $facilitator = User::firstOrCreate(
            ['email' => 'facilitator@zinadauth.com'],
            [
                'name' => 'Workshop Facilitator',
                'password' => Hash::make('password123'),
                'user_name' => 'facilitator',
                'type' => UserTypeEnum::FACILITATOR->value,
                'is_active' => true,
            ]
        );
        if (!$facilitator->hasRole('facilitator')) {
            $facilitator->assignRole('facilitator');
        }

        // Create Participant
        $participant = User::firstOrCreate(
            ['email' => 'participant@zinadauth.com'],
            [
                'name' => 'Test Participant',
                'password' => Hash::make('password123'),
                'user_name' => 'participant',
                'type' => UserTypeEnum::PARTICIPANT->value,
                'is_active' => true,
            ]
        );
        if (!$participant->hasRole('participant')) {
            $participant->assignRole('participant');
        }
    }
}
