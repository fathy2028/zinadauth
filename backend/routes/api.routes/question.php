<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;

Route::group(['prefix' => 'questions', 'as' => 'question.', 'middleware' => 'auth:api'], static function () {
    Route::get('/', [QuestionController::class, 'index']);
    Route::get('/{id}', [QuestionController::class, 'show']);
    Route::get('/type/{type}', [QuestionController::class, 'getByType']);
    Route::get('/random/{type}', [QuestionController::class, 'getRandomByType']);
    Route::get('/statistics', [QuestionController::class, 'statistics']);
    Route::get('/search', [QuestionController::class, 'search']);
    Route::post('/', [QuestionController::class, 'store']);
    Route::post('/{id}/duplicate', [QuestionController::class, 'duplicate']);
    Route::put('/{id}', [QuestionController::class, 'updateQuestion']);
    Route::patch('/{id}', [QuestionController::class, 'updateQuestion']);
    Route::delete('/{id}', [QuestionController::class, 'destroy']);
    Route::post('/bulk-create', [QuestionController::class, 'bulkCreate']);
    Route::post('/bulk-delete', [QuestionController::class, 'bulkDelete']);
});
