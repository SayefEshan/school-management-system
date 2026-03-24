<?php

use Illuminate\Support\Facades\Route;
use Modules\Admission\Http\Controllers\AdmissionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('admissions', AdmissionController::class)->names('admission');
});
