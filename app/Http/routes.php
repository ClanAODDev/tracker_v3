<?php

/**
 * authentication route handler
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



    // API
    Route::get('api/members', 'API\APIController@members');
    Route::get('api/users','API\APIController@users' );

});

