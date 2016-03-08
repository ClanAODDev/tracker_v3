<?php

/**
 * authentication route handler
 */
Route::group(['middleware' => 'web'], function () {

    Route::auth();
    Route::get('/home', 'AppController@index');
    Route::get('/', 'AppController@index');
    Route::get('divisions/{division}', 'DivisionController@show');
    Route::get('send/mail', 'UserController@sendEmailReminder');

    /**
     * Resources
     */
    Route::resource('members', 'MemberController');

});

