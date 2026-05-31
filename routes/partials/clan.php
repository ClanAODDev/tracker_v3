<?php

use App\Http\Controllers\AwardController;
use Illuminate\Support\Facades\Route;

Route::view('clan/ranks', 'clan.ranks')->name('clan.ranks');
Route::view('clan/code-of-conduct', 'clan.code-of-conduct')->name('clan.code-of-conduct');

Route::controller(AwardController::class)->prefix('clan/awards')->name('awards.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('tiered/{slug}', 'tiered')->name('tiered');
    Route::get('{award}', 'show')->name('show');
    Route::post('{award}', 'storeRecommendation')->name('store-recommendation');
});
