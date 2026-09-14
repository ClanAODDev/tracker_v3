<?php

use App\Http\Controllers\Tools\SteamToolController;
use Illuminate\Support\Facades\Route;

Route::prefix('tools')->name('tools.')->group(function () {
    Route::post('steam/resolve-vanity-url', [SteamToolController::class, 'resolveVanityUrl'])
        ->middleware('throttle:20,1')
        ->name('steam.resolve-vanity-url');
});
