<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class UserController extends BaseCrudController
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct(); // Initialize BaseCrudController
    }

    /**
     * Get the model instance for BaseCrudController
     */
    protected function getModel(): User
    {
        return new User();
    }

    
    public function register(RegisterRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Create user using repository (handles password hashing and role assignment)
            $user = $this->userRepository->create($validatedData);

            // Load user with roles for response
            $user->load(['roles', 'permissions']);

            return ApiResponse::success(
                [
                    'user' => new UserResource($user),
                    'capabilities' => $user->getCapabilities(),
                ],
                'User registered successfully',
                201
            );
        } catch (Exception $e) {
            return ApiResponse::error('Registration failed', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Override store method to use RegisterRequest and UserRepository
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Convert Request to RegisterRequest for validation
            $registerRequest = RegisterRequest::createFrom($request);
            $registerRequest->setRouteResolver($request->getRouteResolver());
            $validatedData = $registerRequest->validated();

            // Create user using repository (handles password hashing and role assignment)
            $user = $this->userRepository->create($validatedData);

            // Load user with roles for response
            $user->load(['roles', 'permissions']);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'user' => new UserResource($user),
                    'capabilities' => $user->getCapabilities(),
                ]
            ], 201);

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create user: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }



    /**
     * Override update method to use RegisterRequest and UserRepository
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Convert Request to RegisterRequest for validation
            $registerRequest = RegisterRequest::createFrom($request);
            $registerRequest->setRouteResolver($request->getRouteResolver());
            $validatedData = $registerRequest->validated();

            // Use UserRepository to update the user
            $user = $this->userRepository->update($id, $validatedData);

            // Load user with roles for response
            $user->load(['roles', 'permissions']);

            return ApiResponse::success(
                [
                    'user' => new UserResource($user),
                    'capabilities' => $user->getCapabilities(),
                ],
                'User updated successfully',
                200
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('User not found', 404);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update user', 500, ['exception' => $e->getMessage()]);
        }
    }




    public function login(LoginRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $response = $this->userRepository->login($validatedData);

            if ($response['success']) {
                // Load user with roles and capabilities
                $user = $response['user'];
                $user->load(['roles', 'permissions']);

                return ApiResponse::success([
                    'token' => $response['token'],
                    'user' => new UserResource($user),
                    'capabilities' => $user->getCapabilities(),
                ], 'Login successful', $response['status']);
            } else {
                return ApiResponse::error($response['message'], $response['status']);
            }
        } catch (Exception $e) {
            return ApiResponse::error(
                'Login failed',
                500,
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Logout user (invalidate token)
     */
    public function logout()
    {
        try {
            $response = $this->userRepository->logout();

            return ApiResponse::success(
                null,
                $response->getData()->message,
                $response->getStatusCode()
            );
        } catch (JWTException $e) {
            return ApiResponse::error(
                'Failed to logout. Invalid token.',
                401
            );
        } catch (Exception $e) {
            return ApiResponse::error(
                'Logout failed',
                500,
                ['exception' => $e->getMessage()]
            );
        }
    }


    /**
     * Refresh JWT token
     */
    public function refresh()
    {
        try {
            $token = $this->userRepository->refresh();

            return ApiResponse::success(
                [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ],
                'Token refreshed successfully',
                200
            );
        } catch (JWTException $e) {
            return ApiResponse::error(
                'Failed to refresh token. Invalid or expired token.',
                401
            );
        } catch (Exception $e) {
            return ApiResponse::error(
                'Token refresh failed',
                500,
                ['exception' => $e->getMessage()]
            );
        }
    }
}
