<?php

/**
 * Website specific routes.
 */
Route::prefix('website')->group(function () {
    Route::get('', 'WebsiteController@index')->name('site.home');
});
