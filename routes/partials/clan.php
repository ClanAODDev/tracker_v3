<?php

use App\Http\Controllers\AwardController;
use Illuminate\Support\Facades\Route;

Route::get('clan/ranks', fn () => redirect()->away(config('services.aod_site.url') . '/ranks', 301));
Route::get('clan/code-of-conduct', fn () => redirect()->away(config('services.aod_site.url') . '/code-of-conduct', 301));

Route::controller(AwardController::class)->prefix('clan/awards')->name('awards.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('tiered/{slug}', 'tiered')->name('tiered');
    Route::get('{award}', 'show')->name('show');
    Route::post('{award}', 'storeRecommendation')->name('store-recommendation');
});
