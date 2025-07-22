<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Enums\UserTypeEnum;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new User());
    }
    public function create(array $data): User
    {
        // Hash password
        $data['password'] = Hash::make($data['password']);

        // Set default values
        $data['type'] = $data['type'] ?? UserTypeEnum::PARTICIPANT->value;
        $data['theme'] = $data['theme'] ?? 'light';
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_deleted'] = $data['is_deleted'] ?? false;

        // Create user
        $user = User::create($data);

        // Assign role based on user type
        $this->assignRoleBasedOnType($user);

        return $user;
    }

    /**
     * Update user
     */
    public function update($id, array $data): User
    {
        // Find the user
        $user = User::findOrFail($id);

        // Store original type for role comparison
        $originalType = $user->type;

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Remove password from update data if not provided or empty
            unset($data['password']);
        }

        // Update the user
        $user->update($data);

        // If user type changed, update role
        if (isset($data['type']) && $data['type'] !== $originalType) {
            $this->updateUserRole($user, $data['type']);
        }

        return $user;
    }

    /**
     * Assign role based on user type
     */
    private function assignRoleBasedOnType(User $user): void
    {
        $roleMapping = [
            UserTypeEnum::ADMIN->value => 'admin',
            UserTypeEnum::FACILITATOR->value => 'facilitator',
            UserTypeEnum::PARTICIPANT->value => 'participant',
        ];

        $roleName = $roleMapping[$user->type] ?? 'participant';

        try {
            $user->assignRole($roleName);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to assign role to user', [
                'user_id' => $user->id,
                'user_type' => $user->type,
                'intended_role' => $roleName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update user role when type changes
     */
    private function updateUserRole(User $user, string $newType): void
    {
        $roleMapping = [
            UserTypeEnum::ADMIN->value => 'admin',
            UserTypeEnum::FACILITATOR->value => 'facilitator',
            UserTypeEnum::PARTICIPANT->value => 'participant',
        ];

        $newRoleName = $roleMapping[$newType] ?? 'participant';

        try {
            // Remove all current roles and assign the new one
            $user->syncRoles([$newRoleName]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to update user role', [
                'user_id' => $user->id,
                'old_type' => $user->getOriginal('type'),
                'new_type' => $newType,
                'intended_role' => $newRoleName,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function login(array $credentials)
    {
        $credentials['email'] = strtolower(trim($credentials['email']));

        $token = Auth::guard('api')->attempt($credentials);

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Invalid credentials',
                'status' => 401,
            ];
        }

        return [
            'success' => true,
            'token' => $token,
            'user' => Auth::guard('api')->user(),
            'status' => 200,
        ];
    }

    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'User logged out successfully'], 200);
    }

    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        return $token;
    }
}
