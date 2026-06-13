<?php

use Illuminate\Support\Facades\Route;
use Modules\JsAnalyzer\Http\Controllers\JsAnalyzerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('jsanalyzers', JsAnalyzerController::class)->names('jsanalyzer');
});
