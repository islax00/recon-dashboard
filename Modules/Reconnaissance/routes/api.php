<?php

use Illuminate\Support\Facades\Route;
use Modules\Reconnaissance\Http\Controllers\ReconnaissanceController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('reconnaissances', ReconnaissanceController::class)->names('reconnaissance');
});
