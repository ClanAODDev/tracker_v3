<?php

// @TODO: Convert to markdown
Route::get('changelog', 'AppController@changelog')->name('changelog');
Route::group(['prefix' => 'help/docs'], function () {

    Route::get('/', 'HelpController@index')->name('help');
    Route::view('/division-structures', 'help.division-structures')->name('divisionStructures');
    Route::view('/member-awards', 'help.member-awards')->name('help.member-awards');
    Route::view('/managing-rank', 'help.managing-rank')->name('help.managing-rank');

    /**
     * Admin documentation routes
     *
     * Routes start with /help/admin
     */
    Route::group(['prefix' => 'admin', 'middleware' => ['admin']], function () {

        /**
         * New admin documentation routes should go here
         */
        Route::view('division-checklist', 'help.admin.division-checklist')->name('help.admin.division-checklist');
        Route::view('sink', 'help.admin.sink')->name('help.admin.sink');
        Route::view('', 'help.admin.index')->name('help.admin.home');

    });
});
