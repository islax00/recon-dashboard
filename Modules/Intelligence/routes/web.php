<?php

use Illuminate\Support\Facades\Route;
use Modules\Intelligence\Http\Controllers\IntelligenceController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('intelligences', IntelligenceController::class)->names('intelligence');
});
