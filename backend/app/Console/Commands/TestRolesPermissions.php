<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TestRolesPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:roles-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test roles and permissions implementation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Roles and Permissions Implementation');
        $this->info('==========================================');

        // Test 1: Check if roles were created
        $this->info('1. Testing Roles Creation:');
        $roles = Role::all();
        foreach ($roles as $role) {
            $this->line("   ✓ Role: {$role->name} (Permissions: {$role->permissions->count()})");
        }

        // Test 2: Check if permissions were created
        $this->info('2. Testing Permissions Creation:');
        $permissionCount = Permission::count();
        $this->line("   ✓ Total Permissions: {$permissionCount}");

        // Test 3: Check if users were created with roles
        $this->info('3. Testing Users with Roles:');
        $users = User::with('roles')->get();
        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->join(', ');
            $this->line("   ✓ User: {$user->name} ({$user->email}) - Roles: {$roles}");
        }

        // Test 4: Test specific user capabilities
        $this->info('4. Testing User Capabilities:');
        $superAdmin = User::where('email', 'superadmin@zinadauth.com')->first();
        if ($superAdmin) {
            $capabilities = $superAdmin->getCapabilities();
            $this->line("   ✓ Super Admin Capabilities:");
            foreach ($capabilities as $key => $value) {
                $status = $value ? '✓' : '✗';
                $this->line("     {$status} {$key}: " . ($value ? 'true' : 'false'));
            }
        }

        // Test 5: Test permission checking
        $this->info('5. Testing Permission Checks:');
        $facilitator = User::where('email', 'facilitator@zinadauth.com')->first();
        if ($facilitator) {
            $permissions = ['create-workshops', 'manage-user-roles', 'view-system-statistics'];
            foreach ($permissions as $permission) {
                $hasPermission = $facilitator->can($permission);
                $status = $hasPermission ? '✓' : '✗';
                $this->line("   {$status} Facilitator can {$permission}: " . ($hasPermission ? 'Yes' : 'No'));
            }
        }

        // Test 6: Test participant permissions
        $this->info('6. Testing Participant Permissions:');
        $participant = User::where('email', 'participant@zinadauth.com')->first();
        if ($participant) {
            $permissions = ['view-workshops', 'submit-responses', 'create-workshops'];
            foreach ($permissions as $permission) {
                $hasPermission = $participant->can($permission);
                $status = $hasPermission ? '✓' : '✗';
                $this->line("   {$status} Participant can {$permission}: " . ($hasPermission ? 'Yes' : 'No'));
            }
        }

        $this->info('==========================================');
        $this->info('Roles and Permissions Test Completed!');
    }
}
