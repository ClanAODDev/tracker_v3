<?php

// @TODO: Convert to markdown
Route::get('changelog', 'AppController@changelog')->name('changelog');
Route::group(['prefix' => 'help/docs'], function () {
    
    Route::get('/', 'HelpController@index')->name('help');
    Route::get('/division-structures', 'HelpController@divisionStructures')->name('divisionStructures');


    /**
     * Admin documentation routes
     *
     * Routes start with /help/admin
     */
    Route::group(['prefix' => 'admin', 'middleware' => ['admin']], function () {

        /**
         * New admin documentation routes should go here
         */
        Route::view('sink', 'help.admin.sink')->name('help.admin.sink');
        Route::view('', 'help.admin.index')->name('help.admin.home');

    });
});
