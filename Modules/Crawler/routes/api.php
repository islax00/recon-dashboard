<?php

use Illuminate\Support\Facades\Route;
use Modules\Crawler\Http\Controllers\CrawlerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('crawlers', CrawlerController::class)->names('crawler');
});
