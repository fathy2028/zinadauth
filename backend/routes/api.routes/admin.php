<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::group(['prefix' => 'users', 'as' => 'admin.', 'middleware' => 'role:admin'], static function () {
    Route::apiResource('/', UserController::class)->except(['store']); // store is handled by register
    Route::patch('/{id}/activate', [UserController::class, 'activate'])
        ->name('user.activate');
    Route::patch('/{id}/deactivate', [UserController::class, 'deactivate'])
        ->name('user.deactivate');
    Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])
        ->name('user.bulk-delete');
});
