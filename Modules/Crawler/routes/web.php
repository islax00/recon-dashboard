<?php

use Illuminate\Support\Facades\Route;
use Modules\Crawler\Http\Controllers\CrawlerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('crawlers', CrawlerController::class)->names('crawler');
});
