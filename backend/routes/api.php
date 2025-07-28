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

Route::group(['as' => 'api.'], static function () {
    require __DIR__ . '/api.routes/auth.php';
    require __DIR__ . '/api.routes/workshop.php';
    require __DIR__ . '/api.routes/question.php';
    require __DIR__ . '/api.routes/admin.php';
    require __DIR__ . '/api.routes/assignment.php';
});
