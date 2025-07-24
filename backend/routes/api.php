<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QuestionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/register', [UserController::class, 'register'])->middleware('throttle:3,1');
Route::post('/login', [UserController::class, 'login'])->name('login');

// Protected routes (require JWT token)
Route::middleware('auth:api')->group(function () {
    Route::delete('/logout', [UserController::class, 'logout']);
    Route::post('/refresh', [UserController::class, 'refresh']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Question routes with role-based middleware

    // Routes accessible by all authenticated users (admin, facilitator, participant)
    // These are basic read operations that all users need for taking quizzes/workshops
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::get('/questions/{id}', [QuestionController::class, 'show']);
    Route::get('/questions/type/{type}', [QuestionController::class, 'getByType']);
    Route::get('/questions/random/{type}', [QuestionController::class, 'getRandomByType']);

    // Routes accessible by admin and facilitator only
    // These are content management operations for workshop creators
    Route::middleware('role:admin|facilitator')->group(function () {
        Route::get('/questions-statistics', [QuestionController::class, 'statistics']);
        Route::get('/questions-search', [QuestionController::class, 'search']);
        Route::post('/questions', [QuestionController::class, 'store']);
        Route::post('/questions/{id}/duplicate', [QuestionController::class, 'duplicate']);
        Route::put('/questions/{id}', [QuestionController::class, 'update']);
        Route::patch('/questions/{id}', [QuestionController::class, 'update']);
    });

    // Routes accessible by admin only
    // These are system administration operations with high impact
    Route::middleware('role:admin')->group(function () {
        // Question bulk operations
        Route::post('/questions/bulk-create', [QuestionController::class, 'bulkCreate']);
        Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);
        Route::post('/questions/bulk-delete', [QuestionController::class, 'bulkDelete']);

        // User management routes (admin only)
        Route::apiResource('users', UserController::class)->except(['store']); // store is handled by register
        Route::patch('/users/{id}/activate', [UserController::class, 'activate']);
        Route::patch('/users/{id}/deactivate', [UserController::class, 'deactivate']);
        Route::post('/users/bulk-delete', [UserController::class, 'bulkDelete']);
    });

});