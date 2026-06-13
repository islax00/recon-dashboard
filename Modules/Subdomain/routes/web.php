<?php

use Illuminate\Support\Facades\Route;
use Modules\Subdomain\Http\Controllers\SubdomainController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('subdomains', SubdomainController::class)->names('subdomain');
});
