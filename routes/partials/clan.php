<?php

/**
 * Admin / Member Request routes.
 */
Route::group(['prefix' => 'clan'], function () {
    Route::get('awards', 'AwardController@index')->name('awards.index');
    Route::get('awards/{award}', 'AwardController@show')->name('awards.show');
    Route::post('awards/{award}', 'AwardController@storeRecommendation')->name('awards.store-recommendation');
});
