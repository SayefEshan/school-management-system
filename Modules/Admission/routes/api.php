<?php

use Illuminate\Support\Facades\Route;
use Modules\Admission\Http\Controllers\Api\AdmissionApiController;
use Modules\Admission\Http\Controllers\Api\AdminAdmissionApiController;
use Modules\Admission\Http\Controllers\Api\AcademicStructureController;

/*
|--------------------------------------------------------------------------
| Public API Routes (no auth required)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/admissions')->group(function () {
    // Public: Submit admission application
    Route::post('/', [AdmissionApiController::class, 'store']);
    // Public: Check application status by tracking code
    Route::get('/{tracking_code}/status', [AdmissionApiController::class, 'status']);
});

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // --- Academic Structure (Admin) ---
    Route::prefix('academic')->group(function () {
        Route::get('/years', [AcademicStructureController::class, 'academicYears']);
        Route::post('/years', [AcademicStructureController::class, 'storeAcademicYear']);

        Route::get('/classes', [AcademicStructureController::class, 'classes']);
        Route::post('/classes', [AcademicStructureController::class, 'storeClass']);

        Route::get('/classes/{class_id}/sections', [AcademicStructureController::class, 'sections']);
        Route::post('/sections', [AcademicStructureController::class, 'storeSection']);
    });

    // --- Admin Admission Management ---
    Route::prefix('admin/admissions')->group(function () {
        Route::get('/', [AdminAdmissionApiController::class, 'index']);
        Route::get('/{id}', [AdminAdmissionApiController::class, 'show']);
        Route::post('/{id}/accept', [AdminAdmissionApiController::class, 'accept']);
        Route::post('/{id}/reject', [AdminAdmissionApiController::class, 'reject']);
        Route::post('/{id}/review', [AdminAdmissionApiController::class, 'markUnderReview']);
    });
});
