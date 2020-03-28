<?php

/**
 * Application endpoints
 */
Route::get('/home', 'AppController@index')->name('home');
Route::get('/', 'AppController@index')->name('index');
Route::get('/impersonate-end/', 'ImpersonationController@endImpersonation')->name('end-impersonation');
Route::get('/impersonate/{user}', 'ImpersonationController@impersonate')->name('impersonate');

Route::group(['prefix' => 'training'], function () {
    Route::get('', 'TrainingController@index')->name('training.index');
});

Route::group(['prefix' => 'help', 'middleware' => ['auth']], function () {
    Route::get('tickets', 'TicketController@index')->name('tickets.index');
    Route::get('tickets/{ticket}', 'TicketController@show')->name('tickets.show');
});

Route::group(['prefix' => 'help'], function () {
    Route::get('/', 'HelpController@index')->name('help');
    Route::get('/division-structures', 'HelpController@divisionStructures')->name('divisionStructures');
});

Route::get('changelog', 'AppController@changelog')->name('changelog');
Route::get('developers', 'DeveloperController@index')->name('developer');


/**
 * Application UI
 */
Route::group(['prefix' => 'primary-nav'], function () {
    Route::get('collapse', function () {
        session(['primary_nav_collapsed' => true]);
    });
    Route::get('decollapse', function () {
        session(['primary_nav_collapsed' => false]);
    });
});
