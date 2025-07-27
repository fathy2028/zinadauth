<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::group(['prefix' => 'auth', 'as' => 'auth.', ], static function () {
    Route::post('/register', [UserController::class, 'register'])
        ->middleware('throttle:3,1')->name('register');
    Route::post('/login', [UserController::class, 'login'])
        ->name('login');

    Route::middleware('auth:api')->group(function () {
        Route::delete('/logout', [UserController::class, 'logout'])->name('logout');
        Route::post('/refresh', [UserController::class, 'refresh'])->name('refresh');
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('user');
    });
});
