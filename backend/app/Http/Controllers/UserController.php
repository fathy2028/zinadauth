<?php

namespace App\Http\Controllers;


use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use Exception;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $user = $this->userRepository->create($validatedData);

            return ApiResponse::success(new UserResource($user), 'User created successfully', 201);
        } catch (Exception $e) {
            return ApiResponse::error('Registration failed', 500, ['exception' => $e->getMessage()]);
        }
    }


    public function login(LoginRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $response = $this->userRepository->login($validatedData);

            if ($response['success']) {
                return ApiResponse::success([
                    'token' => $response['token'],
                    'user' => new UserResource($response['user']),
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
