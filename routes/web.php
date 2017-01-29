<?php

Auth::routes();


Route::get('/home', 'AppController@index')->name('home');
Route::get('/', 'AppController@index')->name('index');

Route::get('search/members/{name}', 'MemberController@search')->name('memberSearch');


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
    // divisions
    Route::get('{division}', 'DivisionController@show')->name('division');
    Route::get('{division}/edit', 'DivisionController@edit')->name('editDivision');
    Route::get('{division}/create', 'DivisionController@create')->name('createDivision');
    Route::post('{division}', 'DivisionController@store')->name('storeDivision');
    Route::put('{division}', 'DivisionController@update')->name('updateDivision');
    Route::patch('{division}', 'DivisionController@update');
    Route::delete('{division}', 'DivisionController@destroy')->name('deleteDivision');

    // platoons
    Route::put('{division/platoons', 'PlatoonController@update')->name('updatePlatoon');
    Route::patch('{division/platoons/{platoon}', 'PlatoonController@update');
    Route::delete('{division}/platoons/{platoon}', 'PlatoonController@delete')->name('deletePlatoon');
    Route::get('{division}/platoons/create', 'PlatoonController@create')->name('createPlatoon');
    Route::post('{division}/platoons/store', 'PlatoonController@store')->name('savePlatoon');
    Route::get('{division}/platoons/{platoon}', 'PlatoonController@show')->name('platoon');
    Route::get('{division}/platoons/{platoon}/edit', 'PlatoonController@edit')->name('editPlatoon');
    Route::get('{division}/platoons/{platoon}/squads', 'SquadController@index')->name('platoonSquads');

    // squads
    Route::get('{division}/platoons/{platoon}/squads/create', 'SquadController@create')->name('createSquad');
    Route::delete('{division}/platoons/{platoon}/squads/', 'SquadController@delete')->name('deleteSquad');
    Route::post('{division}/platoons/{platoon}/squads/store', 'SquadController@store')->name('saveSquad');
    Route::get('{division}/platoons/{platoon}/squads/{squad}/edit', 'SquadController@edit')->name('editSquad');
    Route::put('{division}/platoons/{platoon}/squads/{squad}', 'SquadController@update')->name('updateSquad');
    Route::patch('{division}/platoons/{platoon}/squads/{squad}', 'SquadController@update');
    Route::get('{division}/platoons/{platoon}/squads/{squad}', 'SquadController@show')->name('squad');

    // misc routes
    Route::get('{division}/activity', 'ActivitiesController@byDivision')->name('divisionActivity');
    Route::get('{division}/part-timers', 'DivisionController@partTime')->name('partTimers');
    Route::get('{division}/statistics', 'DivisionController@statistics')->name('divisionStats');

});

// logging activity
Route::get('users/{username}/activity', 'ActivitiesController@byUser');

Route::get('developers', 'DeveloperController@index')->name('developer');


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
 * Admin Routes
 */
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
