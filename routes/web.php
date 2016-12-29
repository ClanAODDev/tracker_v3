<?php

Auth::routes();

Route::get('/home', 'AppController@index');
Route::get('/', 'AppController@index');

Route::get('search/members/{name}', 'MemberController@search')->name('memberSearch');

//Route::get('send/mail', 'UserController@sendEmailReminder');


// Members endpoints
Route::group(['prefix' => 'members'], function () {
    Route::get('members/{member}', 'MemberController@show')->name('member');
    Route::get('members/{member}/edit', 'MemberController@edit')->name('editMember');
    Route::delete('members/{member}/delete', 'MemberController@destroy')->name('deleteMember');
});

/**
 * Division endpoints
 */
Route::group(['prefix' => 'divisions/'], function () {
    Route::get('{division}', 'DivisionController@show')->name('division');
    Route::get('{division}/edit', 'DivisionController@edit')->name('editDivision');
    Route::patch('{division}/update', 'DivisionController@update')->name('updateDivision');
    Route::delete('{division}/delete', 'DivisionController@destroy')->name('deleteDivision');
    Route::get('{division}/activity', 'ActivitiesController@byDivision')->name('divisionActivity');
    Route::get('{division}/part-timers', 'DivisionController@partTime')->name('partTimers');
    Route::get('{division}/statistics', 'DivisionController@statistics')->name('divisionStats');

    // platoons
    Route::get('{division}/platoons/create', 'PlatoonController@create')->name('createPlatoon');
    Route::post('{division}/platoons/store', 'PlatoonController@store')->name('savePlatoon');
    Route::get('{division}/platoons/{platoon}', 'PlatoonController@show')->name('platoon');
    Route::get('{division}/platoons/{platoon}/squads', 'SquadController@index')->name('platoonSquads');

    // squads
    Route::get('{division}/platoons/{platoon}/squads/create', 'SquadController@create')->name('createSquad');
    Route::post('{division}/platoons/{platoon}/squads/store', 'SquadController@store')->name('saveSquad');
    Route::get('{division}/squads/{squad}', 'SquadController@show')->name('squad');
});

// logging activity
Route::get('users/{username}/activity', 'ActivitiesController@byUser');


/**
 * Slack handler
 */
Route::group(['middleware' => 'slack'], function () {
    Route::get('slack', [
        'as' => 'slack.commands',
        'uses' => 'SlackController@index',
    ]);
});


/**
 * API ENDPOINTS
 */

Route::group(['prefix' => 'api/v1', 'middleware' => 'throttle:30', 'auth:api'], function () {
    Route::get('divisions', 'API\v1\DivisionController@index');
    Route::get('divisions/{abbreviation}', 'API\v1\DivisionController@show');
});

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::get('/', 'AdminController@index')->name('admin');
    Route::patch('divisions/update', 'AdminController@updateDivisions')->name('updateDivisions');
});

/*
Route::get('all', function() {
    $division = \App\Division::find(4);
    $members = $division->activeMembers;
    $csv = Writer::createFromFileObject(new \SplTempFileObject());

    foreach ($members as $person) {
        $csv->insertOne([$person->name, $person->last_forum_login->format('Y-m-d')]);
    }

    $csv->output('all.csv');
});
*/

/**
 * AOD Forum sync endpoint
 */
/*
Route::group(['prefix' => 'AOD', 'middleware' => 'throttle:15'], function () {
    Route::get('/division-data/{division_name}', function ($division_name) {
        $info = new \App\AOD\MemberSync\GetDivisionInfo($division_name);

        return response()->json($info);
    });
});
*/