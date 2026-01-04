<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

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

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Notification Counts
    Route::get('notification/counts', [NotificationController::class, 'counts'])
        ->name('notification.counts');

    // Only include index, show, and destroy routes
    Route::apiResource('notification', NotificationController::class)
        ->only(['index', 'show', 'destroy'])
        ->names('notification');

    // Additional notification routes
    Route::patch('notification/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])
        ->name('notification.mark-as-read');
    Route::patch('notification/{id}/mark-as-unread', [NotificationController::class, 'markAsUnread'])
        ->name('notification.mark-as-unread');
    Route::patch('notification/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notification.mark-all-as-read');
});
