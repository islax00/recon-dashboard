<?php

use Illuminate\Support\Facades\Route;
use Modules\Reconnaissance\Http\Controllers\ReconnaissanceController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('reconnaissances', ReconnaissanceController::class)->names('reconnaissance');
});
