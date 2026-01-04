<?php

use Illuminate\Support\Facades\Route;
use Modules\ActivityLog\Http\Controllers\ActivityLogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth']], static function () {
    // Activity Log routes
    Route::get('activity-logs/track-ip', [ActivityLogController::class, 'trackIpInfo'])->name('track-ip');
    Route::get('activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
    Route::resource('activity-logs', ActivityLogController::class)->only(['index', 'destroy', 'show'])->names('activity-logs');
});
