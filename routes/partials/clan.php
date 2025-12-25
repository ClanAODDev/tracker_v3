<?php

use App\Http\Controllers\AwardController;
use Illuminate\Support\Facades\Route;

Route::controller(AwardController::class)->prefix('clan/awards')->name('awards.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('{award}', 'show')->name('show');
    Route::post('{award}', 'storeRecommendation')->name('store-recommendation');
});
