<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

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

Route::middleware(['auth', 'verified'])->group(function () {
    // Main notification routes
    Route::resource('notification', NotificationController::class)->only(['index', 'show', 'destroy'])->names('notification');

    // Additional notification actions
    Route::post('notification/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notification.mark-as-read');
    Route::post('notification/{notification}/mark-as-unread', [NotificationController::class, 'markAsUnread'])->name('notification.mark-as-unread');
    Route::post('notification/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notification.mark-all-as-read');
});
