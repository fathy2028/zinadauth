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

    // Question routes with permission middleware
    Route::middleware('permission:view-questions')->group(function () {
        Route::get('/questions', [QuestionController::class, 'index']);
        Route::get('/questions/{id}', [QuestionController::class, 'show']);
        Route::get('/questions/type/{type}', [QuestionController::class, 'getByType']);
        Route::get('/questions/random/{type}', [QuestionController::class, 'getRandomByType']);
        Route::get('/questions-statistics', [QuestionController::class, 'statistics']);
        Route::get('/questions-search', [QuestionController::class, 'search']);
    });

    Route::middleware('permission:create-questions')->group(function () {
        Route::post('/questions', [QuestionController::class, 'store']);
        Route::post('/questions/{id}/duplicate', [QuestionController::class, 'duplicate']);
        Route::post('/questions/bulk-create', [QuestionController::class, 'bulkCreate']);
    });

    Route::middleware('permission:edit-questions')->group(function () {
        Route::put('/questions/{id}', [QuestionController::class, 'update']);
        Route::patch('/questions/{id}', [QuestionController::class, 'update']);
    });

    Route::middleware('permission:delete-questions')->group(function () {
        Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);
        Route::post('/questions/bulk-delete', [QuestionController::class, 'bulkDelete']);
    });

});