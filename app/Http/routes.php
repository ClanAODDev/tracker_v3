<?php

/**
 * Authentication-protected routes
 */
Route::group(['middleware' => 'web'], function () {

    Route::auth();
    Route::get('/home', 'AppController@index');
    Route::get('/', 'AppController@index');

    //Route::get('send/mail', 'UserController@sendEmailReminder');

    // members
    Route::get('members/{member}', 'MemberController@show');

    // divisions
    Route::get('divisions/{division}', 'DivisionController@show');

    // platoons
    Route::get('platoons/{platoon}', 'PlatoonController@show');

});

/**
 * API request routes.
 *
 * Throttle: 5 per minute
 */
Route::group(['prefix' => 'api', 'middleware' => 'throttle:5'], function () {
    Route::get('members', 'API\APIController@members');
    Route::get('users', 'API\APIController@users');
});
