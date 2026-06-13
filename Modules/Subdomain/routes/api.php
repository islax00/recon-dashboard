<?php

use Illuminate\Support\Facades\Route;
use Modules\Subdomain\Http\Controllers\SubdomainController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('subdomains', SubdomainController::class)->names('subdomain');
});
