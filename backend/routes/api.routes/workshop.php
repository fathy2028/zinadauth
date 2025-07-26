<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkshopController;

Route::group(['prefix' => 'workshops', 'as' => 'workshop.', 'middleware' => 'auth:api'], static function () {
    Route::get('/', [WorkshopController::class, 'index'])->name('index');
    Route::get('/{id}', [WorkshopController::class, 'show'])->name('show');
    Route::post('/', [WorkshopController::class, 'store'])->name('store');
    Route::put('/{id}', [WorkshopController::class, 'update'])->name('update');
    Route::delete('/{id}', [WorkshopController::class, 'destroy'])->name('destroy');
});
