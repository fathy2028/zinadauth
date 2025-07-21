<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
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

        // Create roles and assign permissions
        $this->createSuperAdminRole();
        $this->createAdminRole();
        $this->createEducationalDirectorRole();
        $this->createDepartmentHeadRole();
        $this->createFacilitatorRole();
        $this->createContentCreatorRole();
        $this->createParticipantRole();
        
        // Create default users
        $this->createDefaultUsers();
    }

    private function createSuperAdminRole()
    {
        $role = Role::firstOrCreate(['name' => 'super-admin']);
        // Super admin gets all permissions
        $role->syncPermissions(Permission::all());
    }

    private function createAdminRole()
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $permissions = [
            'view-users', 'create-users', 'edit-users', 'delete-users', 'manage-user-roles', 'suspend-users',
            'view-workshops', 'create-workshops', 'edit-workshops', 'delete-workshops', 'manage-workshop-settings',
            'view-assignments', 'create-assignments', 'edit-assignments', 'delete-assignments',
            'view-questions', 'create-questions', 'edit-questions', 'delete-questions',
            'view-contents', 'create-contents', 'edit-contents', 'delete-contents',
            'view-attendance', 'track-attendance', 'monitor-participation',
            'view-progress', 'view-individual-progress', 'view-dashboard-analytics',
            'manage-system-settings', 'configure-branding', 'set-global-timing',
            'view-system-statistics', 'manage-departments', 'assign-administrative-roles',
            'access-api', 'manage-integrations', 'export-data', 'import-data',
        ];
        $role->syncPermissions($permissions);
    }

    private function createEducationalDirectorRole()
    {
        $role = Role::firstOrCreate(['name' => 'educational-director']);
        $permissions = [
            'view-users', 'edit-users', 'manage-user-roles',
            'view-workshops', 'create-workshops', 'edit-workshops', 'manage-workshop-settings',
            'view-assignments', 'create-assignments', 'edit-assignments',
            'view-questions', 'create-questions', 'edit-questions',
            'view-contents', 'create-contents', 'edit-contents',
            'view-attendance', 'track-attendance', 'monitor-participation',
            'view-progress', 'view-individual-progress', 'view-dashboard-analytics',
            'view-workshop-summary', 'compare-workshop-performance', 'export-analytics',
            'manage-departments', 'assign-administrative-roles',
        ];
        $role->syncPermissions($permissions);
    }

    private function createDepartmentHeadRole()
    {
        $role = Role::firstOrCreate(['name' => 'department-head']);
        $permissions = [
            'view-users', 'edit-users',
            'view-workshops', 'create-workshops', 'edit-workshops',
            'view-assignments', 'create-assignments', 'edit-assignments',
            'view-questions', 'create-questions', 'edit-questions',
            'view-contents', 'create-contents', 'edit-contents',
            'view-attendance', 'track-attendance', 'monitor-participation',
            'view-progress', 'view-individual-progress', 'view-dashboard-analytics',
            'suspend-administrative-access',
        ];
        $role->syncPermissions($permissions);
    }

    private function createFacilitatorRole()
    {
        $role = Role::firstOrCreate(['name' => 'facilitator']);
        $permissions = [
            'view-workshops', 'create-workshops', 'edit-workshops',
            'generate-qr-codes', 'generate-pin-codes', 'configure-workshop-branding',
            'view-assignments', 'create-assignments', 'edit-assignments', 'deploy-assignments',
            'randomize-questions', 'manage-assignment-templates',
            'view-questions', 'create-questions', 'edit-questions',
            'set-question-duration', 'assign-point-values',
            'view-contents', 'create-contents', 'edit-contents', 'create-templates',
            'manage-powerpoint-integration',
            'view-attendance', 'track-attendance', 'confirm-attendance-pin', 'monitor-participation',
            'view-real-time-responses', 'activate-real-time-questions', 'monitor-live-sessions',
            'adjust-content-delivery', 'manage-synchronized-sessions',
            'view-progress', 'view-individual-progress', 'view-leaderboard',
            'view-workshop-analytics', 'view-workshop-summary',
            'grade-responses', 'provide-feedback', 'view-assessment-results',
            'access-mobile-features', 'sync-across-devices',
        ];
        $role->syncPermissions($permissions);
    }

    private function createContentCreatorRole()
    {
        $role = Role::firstOrCreate(['name' => 'content-creator']);
        $permissions = [
            'view-assignments', 'create-assignments', 'edit-assignments',
            'manage-assignment-templates',
            'view-questions', 'create-questions', 'edit-questions',
            'create-multilingual-questions', 'set-question-duration', 'assign-point-values',
            'view-contents', 'create-contents', 'edit-contents', 'create-templates',
            'create-multilingual-content', 'manage-powerpoint-integration',
            'access-mobile-features', 'sync-across-devices',
        ];
        $role->syncPermissions($permissions);
    }

    private function createParticipantRole()
    {
        $role = Role::firstOrCreate(['name' => 'participant']);
        $permissions = [
            'view-workshops',
            'view-assignments',
            'submit-responses',
            'view-responses',
            'view-assessment-results',
            'confirm-attendance-pin',
            'view-progress',
            'view-leaderboard',
            'access-mobile-features',
            'sync-across-devices',
            'use-qr-code-joining',
        ];
        $role->syncPermissions($permissions);
    }

    private function createDefaultUsers()
    {
        // Create Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@zinadauth.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'user_name' => 'superadmin',
                'type' => 'admin',
                'is_active' => true,
            ]
        );
        if (!$superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole('super-admin');
        }

        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@zinadauth.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'user_name' => 'admin',
                'type' => 'admin',
                'is_active' => true,
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create Educational Director
        $director = User::firstOrCreate(
            ['email' => 'director@zinadauth.com'],
            [
                'name' => 'Educational Director',
                'password' => Hash::make('password123'),
                'user_name' => 'director',
                'type' => 'admin',
                'is_active' => true,
            ]
        );
        if (!$director->hasRole('educational-director')) {
            $director->assignRole('educational-director');
        }

        // Create Department Head
        $deptHead = User::firstOrCreate(
            ['email' => 'depthead@zinadauth.com'],
            [
                'name' => 'Department Head',
                'password' => Hash::make('password123'),
                'user_name' => 'depthead',
                'type' => 'facilitator',
                'is_active' => true,
            ]
        );
        if (!$deptHead->hasRole('department-head')) {
            $deptHead->assignRole('department-head');
        }

        // Create Facilitator
        $facilitator = User::firstOrCreate(
            ['email' => 'facilitator@zinadauth.com'],
            [
                'name' => 'Workshop Facilitator',
                'password' => Hash::make('password123'),
                'user_name' => 'facilitator',
                'type' => 'facilitator',
                'is_active' => true,
            ]
        );
        if (!$facilitator->hasRole('facilitator')) {
            $facilitator->assignRole('facilitator');
        }

        // Create Content Creator
        $contentCreator = User::firstOrCreate(
            ['email' => 'creator@zinadauth.com'],
            [
                'name' => 'Content Creator',
                'password' => Hash::make('password123'),
                'user_name' => 'creator',
                'type' => 'facilitator',
                'is_active' => true,
            ]
        );
        if (!$contentCreator->hasRole('content-creator')) {
            $contentCreator->assignRole('content-creator');
        }

        // Create Participant
        $participant = User::firstOrCreate(
            ['email' => 'participant@zinadauth.com'],
            [
                'name' => 'Test Participant',
                'password' => Hash::make('password123'),
                'user_name' => 'participant',
                'type' => 'participant',
                'is_active' => true,
            ]
        );
        if (!$participant->hasRole('participant')) {
            $participant->assignRole('participant');
        }
    }
}
