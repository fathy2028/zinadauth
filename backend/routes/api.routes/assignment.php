<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssignmentController;

Route::group(['prefix' => 'assignments', 'as' => 'assignment.', 'middleware' => 'auth:api'], static function () {
    Route::get('/', [AssignmentController::class, 'index'])->name('index');
    Route::get('/{id}', [AssignmentController::class, 'show'])->name('show');
    Route::post('/', [AssignmentController::class, 'store'])->name('store');
    Route::put('/{id}', [AssignmentController::class, 'update'])->name('update');
    Route::delete('/{id}', [AssignmentController::class, 'destroy'])->name('destroy');
});
