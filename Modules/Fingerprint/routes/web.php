<?php

use Illuminate\Support\Facades\Route;
use Modules\Fingerprint\Http\Controllers\FingerprintController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('fingerprints', FingerprintController::class)->names('fingerprint');
});
