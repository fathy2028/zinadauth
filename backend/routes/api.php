<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolePermissionController;

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

    // Role and Permission Management Routes
    Route::prefix('roles-permissions')->group(function () {
        // Get current user capabilities (available to all authenticated users)
        Route::get('/my-capabilities', [RolePermissionController::class, 'getMyCapabilities']);

        // Admin only routes
        Route::middleware('permission:manage-user-roles')->group(function () {
            Route::get('/roles', [RolePermissionController::class, 'getRoles']);
            Route::get('/permissions', [RolePermissionController::class, 'getPermissions']);
            Route::post('/assign-role', [RolePermissionController::class, 'assignRole']);
            Route::post('/remove-role', [RolePermissionController::class, 'removeRole']);
            Route::post('/give-permission', [RolePermissionController::class, 'givePermission']);
            Route::post('/revoke-permission', [RolePermissionController::class, 'revokePermission']);
            Route::get('/user/{userId}/roles-permissions', [RolePermissionController::class, 'getUserRolesPermissions']);
            Route::post('/check-permission', [RolePermissionController::class, 'checkPermission']);
            Route::post('/check-role', [RolePermissionController::class, 'checkRole']);
        });
    });
});