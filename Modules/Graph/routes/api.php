<?php

use Illuminate\Support\Facades\Route;
use Modules\Graph\Http\Controllers\GraphController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('graphs', GraphController::class)->names('graph');
});
