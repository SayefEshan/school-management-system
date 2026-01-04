<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

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

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
    Route::get('reset/password/{id}', [UserController::class, 'resetPassword'])->name('user.password.reset');
    Route::get('users-export', [UserController::class, 'export'])->name('users.export');
    Route::post('users-bulk-upload', [UserController::class, 'bulkUpload'])->name('users.bulk');
    Route::post('users/{id}/status', [UserController::class, 'updateStatus'])->name('users.status');

    // User account management route (reset/delete)
    Route::post('users/{id}/manage-account', [UserController::class, 'manageAccount'])->name('users.account.manage');
    
    // Document Upload
    Route::post('users/{user}/documents', [UserController::class, 'uploadDocument'])->name('users.documents.store');
    

});
