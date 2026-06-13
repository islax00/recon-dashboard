<?php

use Illuminate\Support\Facades\Route;
use Modules\Intelligence\Http\Controllers\ChatController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('scans/{scan}/chat', [ChatController::class, 'store'])->name('scans.chat');
});
