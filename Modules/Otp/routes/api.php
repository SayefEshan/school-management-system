<?php

use Illuminate\Support\Facades\Route;
use Modules\Otp\Http\Controllers\OtpController;
use Modules\Otp\Http\Controllers\OtpVerificationController;
use Modules\Otp\Http\Controllers\OtpWhitelistController;

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
    // Public OTP verification routes
    Route::post('send-verification-code', [OtpVerificationController::class, 'sendVerificationCode'])->middleware('throttle:10,1');
    Route::post('verify-otp', [OtpVerificationController::class, 'verifyCode']);

    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::apiResource('otp-whitelist', OtpWhitelistController::class)->names('otp-whitelist');
    });
});
