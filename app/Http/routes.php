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
    Route::get('divisions/{division}/squads/', 'DivisionController@squads');

    // platoons
    Route::get('platoons/{platoon}', 'PlatoonController@show');
    Route::get('platoons/{platoon}/squads', 'PlatoonController@squads');

    // squads
    Route::get('squads/{squad}', 'SquadController@show');

});

/**
 * API request routes.
 */
Route::group(['prefix' => 'v1/api', 'middleware' => 'throttle:30'], function () {
    Route::get('members', 'API\APIController@members');
    Route::get('users', 'API\APIController@users');
    Route::get('divisions', 'API\APIController@divisions');
    Route::get('squads', 'API\APIController@squads');
    Route::get('platoons', 'API\APIController@platoons');
});
