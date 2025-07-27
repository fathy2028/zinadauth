<?php

namespace App\Support\Traits;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait HandlesExceptions
{
    // TODO: make Exceptions Mapper.
    /**
     * Handle common exceptions and return appropriate JSON responses
     */
    protected function handleException(\Exception $e, string $operation = 'operation', array $context = []): JsonResponse
    {
        if ($e instanceof AuthorizationException) {
            return ApiResponse::error(
                message: 'Unauthorized access',
                code: 403
            );
        }

        if ($e instanceof ValidationException) {
            return ApiResponse::error(
                message: 'Validation failed',
                code: 422,
                errors: $e->errors()
            );
        }

        if ($e instanceof ModelNotFoundException) {
            return ApiResponse::error(
                message: 'Record not found',
                code: 404
            );
        }

        // Log unexpected errors
        Log::error("Failed to {$operation}: " . $e->getMessage(), array_merge([
            'model' => get_class($this->model),
        ], $context));

        return ApiResponse::error(
            message: "Failed to {$operation}",
            code: 500,
            errors: config('app.debug') ? $e->getMessage() : 'Something went wrong'
        );
    }
}
