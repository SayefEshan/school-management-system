<?php

use Illuminate\Support\Facades\Route;
use Modules\ImportDownloadManager\Http\Controllers\DownloadImportManagerController;

Route::prefix('download-import-manager')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DownloadImportManagerController::class, 'index'])->name('download.import.manager.index');
    Route::delete('/delete/{id}', [DownloadImportManagerController::class, 'destroy'])->name('download.import.manager.delete');
    Route::get('/get-update-status', [DownloadImportManagerController::class, 'getStatusUpdate'])->name('download.import.get.update');
    Route::get('download/{id}', [DownloadImportManagerController::class, 'downloadFile'])->name('download.import.manager.download');
});
