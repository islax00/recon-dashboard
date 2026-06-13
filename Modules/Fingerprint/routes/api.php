<?php

use Illuminate\Support\Facades\Route;
use Modules\Fingerprint\Http\Controllers\FingerprintController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('fingerprints', FingerprintController::class)->names('fingerprint');
});
