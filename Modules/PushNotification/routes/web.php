<?php

use Illuminate\Support\Facades\Route;
use Modules\PushNotification\Http\Controllers\PushNotificationController;

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

Route::group(['middleware' => ['auth']], function () {
    Route::get('push-notifications', [PushNotificationController::class, 'index'])->name('push.notification.index');
    Route::get('push-notification/create', [PushNotificationController::class, 'create'])->name('push.notification.create');
    Route::post('push-notification/create', [PushNotificationController::class, 'store'])->name('push.notification.store');
});

