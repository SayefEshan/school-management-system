<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsController;
use Modules\Settings\Http\Controllers\SpecialSettingsController;

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
    // Regular settings routes
    Route::get("system-settings", [SettingsController::class, 'index'])->name("system_settings.index");
    Route::post("system-settings", [SettingsController::class, 'store'])->name("system_settings.store");

    // New routes for managing settings entries
    Route::get("system-settings/manage", [SettingsController::class, 'manage'])->name("system_settings.manage");
    Route::get("system-settings/create", [SettingsController::class, 'create'])->name("system_settings.create");
    Route::post("system-settings/create", [SettingsController::class, 'storeNew'])->name("system_settings.store_new");
    Route::get("system-settings/{setting}/edit", [SettingsController::class, 'edit'])->name("system_settings.edit");
    Route::put("system-settings/{setting}", [SettingsController::class, 'update'])->name("system_settings.update");
    Route::delete("system-settings/{setting}", [SettingsController::class, 'destroy'])->name("system_settings.destroy");

    // Import and export routes
    Route::get("system-settings/export", [SettingsController::class, 'export'])->name("system_settings.export");
    Route::get("system-settings/import", [SettingsController::class, 'importForm'])->name("system_settings.import_form");
    Route::post("system-settings/import", [SettingsController::class, 'import'])->name("system_settings.import");

    // Bulk operations routes
    Route::put("system-settings/bulk-update", [SettingsController::class, 'bulkUpdate'])->name("system_settings.bulk_update");
    Route::delete("system-settings/bulk-delete", [SettingsController::class, 'bulkDelete'])->name("system_settings.bulk_delete");

    // Special settings routes
    Route::prefix('special-settings')->name('special_settings.')->group(function () {
        // Privacy Policy routes
        Route::get("privacy-policy", [SpecialSettingsController::class, 'privacyPolicy'])->name("privacy_policy");
        Route::post("privacy-policy", [SpecialSettingsController::class, 'updatePrivacyPolicy'])->name("update_privacy_policy");

        // SMS Gateways routes
        Route::get("sms-gateways", [SpecialSettingsController::class, 'smsGateways'])->name("sms_gateways");
        Route::post("sms-gateways", [SpecialSettingsController::class, 'updateSmsGateways'])->name("update_sms_gateways");
        Route::post("test-sms", [SpecialSettingsController::class, 'sendTestSMS'])->name("send_test_sms");

        // Email Mailers routes
        Route::get("email-mailers", [SpecialSettingsController::class, 'emailMailers'])->name("email_mailers");
        Route::post("email-mailers", [SpecialSettingsController::class, 'updateEmailMailers'])->name("update_email_mailers");
        Route::post("test-email", [SpecialSettingsController::class, 'sendTestEmail'])->name("send_test_email");
    });
});
