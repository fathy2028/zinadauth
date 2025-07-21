<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    /**
     * Get all roles with their permissions
     */
    public function getRoles(): JsonResponse
    {
        try {
            $roles = Role::with('permissions')->get();
            
            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all permissions
     */
    public function getPermissions(): JsonResponse
    {
        try {
            $permissions = Permission::all();
            
            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_name' => 'required|exists:spatie_roles,name'
            ]);

            $user = User::findOrFail($request->user_id);
            $user->assignRole($request->role_name);

            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully',
                'data' => [
                    'user' => $user->load('roles'),
                    'capabilities' => $user->getCapabilities()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_name' => 'required|exists:spatie_roles,name'
            ]);

            $user = User::findOrFail($request->user_id);
            $user->removeRole($request->role_name);

            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully',
                'data' => [
                    'user' => $user->load('roles'),
                    'capabilities' => $user->getCapabilities()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Give permission to user
     */
    public function givePermission(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'permission_name' => 'required|exists:spatie_permissions,name'
            ]);

            $user = User::findOrFail($request->user_id);
            $user->givePermissionTo($request->permission_name);

            return response()->json([
                'success' => true,
                'message' => 'Permission granted successfully',
                'data' => [
                    'user' => $user->load('permissions'),
                    'capabilities' => $user->getCapabilities()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to grant permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke permission from user
     */
    public function revokePermission(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'permission_name' => 'required|exists:spatie_permissions,name'
            ]);

            $user = User::findOrFail($request->user_id);
            $user->revokePermissionTo($request->permission_name);

            return response()->json([
                'success' => true,
                'message' => 'Permission revoked successfully',
                'data' => [
                    'user' => $user->load('permissions'),
                    'capabilities' => $user->getCapabilities()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's roles and permissions
     */
    public function getUserRolesPermissions($userId): JsonResponse
    {
        try {
            $user = User::with(['roles', 'permissions'])->findOrFail($userId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'capabilities' => $user->getCapabilities(),
                    'all_permissions' => $user->getAllPermissions(),
                    'direct_permissions' => $user->getDirectPermissions(),
                    'permissions_via_roles' => $user->getPermissionsViaRoles(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user roles and permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has permission
     */
    public function checkPermission(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'permission_name' => 'required|string'
            ]);

            $user = User::findOrFail($request->user_id);
            $hasPermission = $user->can($request->permission_name);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'permission' => $request->permission_name,
                    'has_permission' => $hasPermission
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has role
     */
    public function checkRole(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_name' => 'required|string'
            ]);

            $user = User::findOrFail($request->user_id);
            $hasRole = $user->hasRole($request->role_name);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'role' => $request->role_name,
                    'has_role' => $hasRole
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user's capabilities
     */
    public function getMyCapabilities(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user->load(['roles', 'permissions']),
                    'capabilities' => $user->getCapabilities(),
                    'all_permissions' => $user->getAllPermissions()->pluck('name'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch capabilities',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
