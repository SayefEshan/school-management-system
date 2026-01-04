<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\Api\ApiSettingsController;

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

Route::prefix('v1/settings/app')->group(function () {
    Route::get('/', [ApiSettingsController::class, 'appSettings']);
});
