<?php

use Illuminate\Support\Facades\Route;
use Modules\Graph\Http\Controllers\GraphController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('graphs', GraphController::class)->names('graph');
});
