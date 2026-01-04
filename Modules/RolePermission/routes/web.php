<?php

use Illuminate\Support\Facades\Route;
use Modules\RolePermission\Http\Controllers\RolePermissionController;

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
    Route::get('role/assign/permission/{id}', [RolePermissionController::class, 'assignPermissionPage'])->name('role.assign.permission.get');
    Route::post('role/assign/permission/{id}', [RolePermissionController::class, 'assignPermissionStore'])->name('role.assign.permission');

    // Routes for managing permissions
    Route::get('permissions', [RolePermissionController::class, 'managePermissions'])->name('permissions.manage');
    Route::delete('permission/{id}', [RolePermissionController::class, 'deletePermission'])->name('permission.delete');
    Route::get('permissions/sync', [RolePermissionController::class, 'syncPermissions'])->name('permission.sync');
    Route::post('permission', [RolePermissionController::class, 'createPermission'])->name('permission.store');
    Route::delete('permissions/bulk-delete', [RolePermissionController::class, 'bulkDeletePermissions'])->name('permission.bulk-delete');
    Route::get('permission/{id}/roles', [RolePermissionController::class, 'getPermissionRoles'])->name('permission.roles');

    // Permission matrix
    Route::get('permissions/matrix', [RolePermissionController::class, 'permissionMatrix'])->name('permission.matrix');
    Route::post('permissions/matrix/update', [RolePermissionController::class, 'updatePermissionMatrix'])->name('permission.matrix.update');

    // Role cloning
    Route::get('role/{id}/clone', [RolePermissionController::class, 'cloneRole'])->name('role.clone');

    Route::resource('role', RolePermissionController::class);
});
