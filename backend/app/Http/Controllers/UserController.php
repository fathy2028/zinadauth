<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Actions\Auth\CreateUserAction;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public function register(RegisterRequest $request) {
        try {
            $validatedData = $request->validated();

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // Remove sensitive data from response
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => $userData
            ], 201);

        } catch (\Exception $e) {
            $this->logSecurityEvent('Registration failed with exception', $request, [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'User registration failed. Please try again later.',
            ], 500);
        }
    }


    public function login(LoginRequest $request)
    {
        try {
            // Validate request data
            $validatedData = $request->validated();

            // Sanitize credentials
            $credentials = [
                'email' => strtolower(trim($validatedData['email'])),
                'password' => $validatedData['password'] // Don't sanitize password
            ];

            // Attempt to create a token
            $token = Auth::guard('api')->attempt($credentials);
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }


            // Get the authenticated user
            $user = auth()->user();
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => $userData,
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication service temporarily unavailable'
            ], 500);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed. Please try again later.'
            ], 500);
        }
    }

    /**
     * Logout user (invalidate token)
     */
    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            JWTAuth::invalidate($token);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to logout. Please try again later.'
            ], 500);
        }
    }


    /**
     * Refresh JWT token
     */
    public function refresh(Request $request)
    {
        try {
            $user = auth()->user();
            $userId = $user ? $user->id : null;

            $token = JWTAuth::refresh(JWTAuth::getToken());

            $this->logSecurityEvent('Token refreshed successfully', $request, [
                'user_id' => $userId
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token cannot be refreshed'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token refresh failed'
            ], 500);
        }
    }
}
