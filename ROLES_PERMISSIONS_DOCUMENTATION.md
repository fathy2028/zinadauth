# Roles and Permissions Implementation

## Overview

This document describes the comprehensive roles and permissions system implemented for the ZinadAuth educational workshop management platform using the Spatie Laravel Permission package.

## System Architecture

### User Roles Hierarchy

1. **Super Admin** - Complete system access
2. **Admin** - System administration and management
3. **Educational Director** - Educational oversight and analytics
4. **Department Head** - Department-level management
5. **Facilitator** - Workshop creation and management
6. **Content Creator** - Content and assignment development
7. **Participant** - Workshop participation and learning

### Permission Categories

#### User Management
- `view-users`, `create-users`, `edit-users`, `delete-users`
- `manage-user-roles`, `suspend-users`

#### Workshop Management
- `view-workshops`, `create-workshops`, `edit-workshops`, `delete-workshops`
- `manage-workshop-settings`, `generate-qr-codes`, `generate-pin-codes`
- `configure-workshop-branding`, `view-workshop-analytics`

#### Assignment Management
- `view-assignments`, `create-assignments`, `edit-assignments`, `delete-assignments`
- `deploy-assignments`, `randomize-questions`, `manage-assignment-templates`

#### Question Management
- `view-questions`, `create-questions`, `edit-questions`, `delete-questions`
- `create-multilingual-questions`, `set-question-duration`, `assign-point-values`

#### Content Management
- `view-contents`, `create-contents`, `edit-contents`, `delete-contents`
- `create-templates`, `manage-powerpoint-integration`, `create-multilingual-content`

#### Attendance & Participation
- `view-attendance`, `track-attendance`, `confirm-attendance-pin`
- `monitor-participation`, `view-real-time-responses`

#### Progress & Analytics
- `view-progress`, `view-individual-progress`, `view-leaderboard`
- `view-dashboard-analytics`, `view-workshop-summary`
- `compare-workshop-performance`, `export-analytics`

#### Assessment & Grading
- `submit-responses`, `view-responses`, `grade-responses`
- `provide-feedback`, `view-assessment-results`

#### System Configuration
- `manage-system-settings`, `configure-branding`, `set-global-timing`
- `manage-language-settings`, `configure-domain-settings`

#### Administrative Functions
- `view-system-statistics`, `manage-departments`
- `assign-administrative-roles`, `suspend-administrative-access`, `view-audit-logs`

#### Real-time Collaboration
- `activate-real-time-questions`, `monitor-live-sessions`
- `adjust-content-delivery`, `manage-synchronized-sessions`

#### Multi-device Support
- `access-mobile-features`, `sync-across-devices`, `use-qr-code-joining`

#### Integration & API
- `access-api`, `manage-integrations`, `export-data`, `import-data`

## Implementation Details

### Database Structure

The system uses Spatie Laravel Permission package with custom table names to avoid conflicts:
- `spatie_roles` - Stores role definitions
- `spatie_permissions` - Stores permission definitions
- `spatie_role_has_permissions` - Role-permission relationships
- `spatie_model_has_roles` - User-role relationships
- `spatie_model_has_permissions` - Direct user-permission relationships

### User Model Integration

The User model includes:
- `HasRoles` trait from Spatie
- `HasRolePermissions` custom trait for helper methods
- UUID primary key support
- Automatic role-based capabilities

### API Endpoints

#### Role and Permission Management
- `GET /api/roles-permissions/roles` - Get all roles with permissions
- `GET /api/roles-permissions/permissions` - Get all permissions
- `POST /api/roles-permissions/assign-role` - Assign role to user
- `POST /api/roles-permissions/remove-role` - Remove role from user
- `POST /api/roles-permissions/give-permission` - Give direct permission
- `POST /api/roles-permissions/revoke-permission` - Revoke direct permission
- `GET /api/roles-permissions/user/{userId}/roles-permissions` - Get user's roles and permissions
- `POST /api/roles-permissions/check-permission` - Check if user has permission
- `POST /api/roles-permissions/check-role` - Check if user has role
- `GET /api/roles-permissions/my-capabilities` - Get current user's capabilities

### Middleware Protection

Routes can be protected using:
- `permission:permission-name` - Requires specific permission
- `role:role-name` - Requires specific role
- `role_or_permission:role-name|permission-name` - Requires either role or permission

### Helper Methods

The `HasRolePermissions` trait provides convenient methods:
- `isSuperAdmin()`, `isAdmin()`, `isFacilitator()`, `isContentCreator()`, `isParticipant()`
- `canManageWorkshops()`, `canManageAssignments()`, `canManageUsers()`
- `canViewAnalytics()`, `canManageSystem()`
- `getPrimaryRole()`, `getCapabilities()`

## Default Users Created

The seeder creates default users for testing:
- **superadmin@zinadauth.com** (Super Admin) - password123
- **admin@zinadauth.com** (Admin) - password123
- **director@zinadauth.com** (Educational Director) - password123
- **depthead@zinadauth.com** (Department Head) - password123
- **facilitator@zinadauth.com** (Facilitator) - password123
- **creator@zinadauth.com** (Content Creator) - password123
- **participant@zinadauth.com** (Participant) - password123

## Usage Examples

### Checking Permissions in Controllers
```php
// Check if user can create workshops
if (auth()->user()->can('create-workshops')) {
    // Allow workshop creation
}

// Check if user is facilitator
if (auth()->user()->isFacilitator()) {
    // Show facilitator features
}
```

### Protecting Routes
```php
Route::middleware(['auth:api', 'permission:manage-workshops'])->group(function () {
    Route::post('/workshops', [WorkshopController::class, 'store']);
});
```

### Assigning Roles Programmatically
```php
$user = User::find($userId);
$user->assignRole('facilitator');
$user->givePermissionTo('create-workshops');
```

## Testing

Run the test command to verify the implementation:
```bash
php artisan test:roles-permissions
```

This command tests:
- Role creation and permission assignment
- User creation with roles
- Permission checking functionality
- User capabilities
- Role hierarchy validation

## Security Considerations

1. **Principle of Least Privilege** - Users only get minimum required permissions
2. **Role Hierarchy** - Higher roles inherit appropriate lower-level permissions
3. **Permission Granularity** - Fine-grained permissions for precise access control
4. **API Protection** - All sensitive endpoints require authentication and authorization
5. **Audit Trail** - System tracks role and permission changes

## Maintenance

### Adding New Permissions
1. Add permission to the `$permissions` array in `RolePermissionSeeder`
2. Assign to appropriate roles in role creation methods
3. Run `php artisan db:seed --class=RolePermissionSeeder`

### Adding New Roles
1. Create new role method in `RolePermissionSeeder`
2. Define permissions for the role
3. Call the method in the `run()` method
4. Update helper methods in `HasRolePermissions` trait if needed

### Modifying Existing Roles
1. Update the role's permission array in the seeder
2. Re-run the seeder to sync permissions
3. Test affected functionality

This implementation provides a robust, scalable, and secure foundation for managing user access in the educational workshop platform.
