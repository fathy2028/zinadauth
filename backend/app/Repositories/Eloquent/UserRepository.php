<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return $user;
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
        $user = auth()->user();
        $userId = $user ? $user->id : null;

        $token = JWTAuth::refresh(JWTAuth::getToken());
    }
}
