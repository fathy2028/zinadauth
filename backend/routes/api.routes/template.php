<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;

Route::group(['prefix' => 'templates', 'as' => 'template.', 'middleware' => 'auth:api'], static function () {
    Route::get('/', [TemplateController::class, 'index']);
    Route::get('/{id}', [TemplateController::class, 'show']);
    Route::post('/', [TemplateController::class, 'store']);
    Route::put('/{id}', [TemplateController::class, 'update']);
    Route::delete('/{id}', [TemplateController::class, 'destroy']);
});