<?php

use Illuminate\Support\Facades\Route;
use Modules\JsAnalyzer\Http\Controllers\JsAnalyzerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('jsanalyzers', JsAnalyzerController::class)->names('jsanalyzer');
});
