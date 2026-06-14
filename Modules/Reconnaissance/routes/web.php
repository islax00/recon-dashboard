<?php

use Illuminate\Support\Facades\Route;
use Modules\Reconnaissance\Http\Controllers\ScanController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('scans', [ScanController::class, 'index'])->name('scans.index');
    Route::post('scans', [ScanController::class, 'store'])->name('scans.store');
    Route::get('scans/{scan}', [ScanController::class, 'show'])->name('scans.show');
});
