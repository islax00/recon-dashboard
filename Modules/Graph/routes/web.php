<?php

use Illuminate\Support\Facades\Route;
use Modules\Graph\Http\Controllers\GraphController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('scans/{scan}/graph', [GraphController::class, 'show'])->name('scans.graph');
});
