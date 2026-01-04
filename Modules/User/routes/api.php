<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\Api\UserController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::prefix('v1')->group(function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'register']);
    Route::post('reset-password', [UserController::class, 'resetPassword']);
    Route::post('auth/social', [UserController::class, 'socialAuth']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user/documents', [\Modules\User\Http\Controllers\Api\UserDocumentController::class, 'index']);
        Route::post('user/documents', [\Modules\User\Http\Controllers\Api\UserDocumentController::class, 'store']);

        Route::get('profile', [UserController::class, 'me']);
        Route::patch('profile', [UserController::class, 'update']);
        Route::post('logout', [UserController::class, 'logout']);
        Route::post('change-password', [UserController::class, 'changePassword']);
        Route::post('manage-account', [UserController::class, 'manageAccount']);
    });
});
