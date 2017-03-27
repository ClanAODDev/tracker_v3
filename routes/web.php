<?php

Auth::routes();

Route::get('/home', 'AppController@index')->name('home');
Route::get('/', 'AppController@index')->name('index');

Route::get('search/members/{name}', 'MemberController@search')->name('memberSearch');


// Members endpoints
Route::group(['prefix' => 'members'], function () {
    Route::get('{member}', 'MemberController@show')->name('member');
    Route::get('{member}/edit', 'MemberController@edit')->name('editMember');
    Route::post('search/{name}', 'MemberController@search');
    Route::delete('{member}', 'MemberController@destroy')->name('deleteMember');
});

Route::group(['prefix' => 'help'], function () {
    Route::get('/', 'HelpController@index')->name('help');
});

Route::group(['prefix' => 'statistics'], function () {
    Route::get('/', 'ClanStatisticsController@show')->name('statistics');
});

/**
 * Division endpoints
 */
Route::group(['prefix' => 'divisions/'], function () {

    /**
     * divisions
     */
    Route::get('{division}', 'DivisionController@show')->name('division');
    Route::get('{division}/edit', 'DivisionController@edit')->name('editDivision');
    Route::get('{division}/create', 'DivisionController@create')->name('createDivision');

    Route::post('', 'DivisionController@store')->name('storeDivision');
    Route::put('{division}', 'DivisionController@update')->name('updateDivision');
    Route::patch('{division}', 'DivisionController@update');
    Route::delete('{division}', 'DivisionController@destroy')->name('deleteDivision');

    Route::group(['prefix' => '{division}/census/'], function() {
       Route::get('', 'CensusController@show')->name('census.show');
    });

    /**
     * platoons
     */
    Route::group(['prefix' => '{division}/platoons/'], function () {

        Route::get('/create', 'PlatoonController@create')->name('createPlatoon');
        Route::get('{platoon}', 'PlatoonController@show')->name('platoon');
        Route::get('{platoon}/edit', 'PlatoonController@edit')->name('editPlatoon');
        Route::get('{platoon}/squads', 'PlatoonController@squads')->name('platoonSquads');

        Route::post('', 'PlatoonController@store')->name('savePlatoon');
        Route::put('{platoon}', 'PlatoonController@update')->name('updatePlatoon');
        Route::patch('{platoon}', 'PlatoonController@update');
        Route::delete('{platoon}', 'PlatoonController@destroy')->name('deletePlatoon');


        /**
         * squads
         */
        Route::group(['prefix' => '{platoon}/squads/'], function () {
            Route::get('/create', 'SquadController@create')->name('createSquad');
            Route::get('{squad}', 'SquadController@show')->name('squad');
            Route::get('{squad}/edit', 'SquadController@edit')->name('editSquad');

            Route::post('', 'SquadController@store')->name('storeSquad');
            Route::put('{squad}', 'SquadController@update')->name('updateSquad');
            Route::patch('{squad}', 'SquadController@update');
            Route::delete('{squad}', 'SquadController@destroy')->name('deleteSquad');
        });

    });


    /**
     * Misc Routes
     */
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
Route::post('slack', [
    'as' => 'slack.commands',
    'uses' => 'SlackController@index',
])->middleware('slack');


/**
 * Admin Routes
 */
Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::get('/', 'AdminController@index')->name('admin');

    // edit division (admin)
    Route::get('divisions/{division}/edit', 'AdminController@editDivision')->name('adminEditDivision');
    Route::put('divisions/{division}', 'AdminController@updateDivision')->name('adminUpdateDivision');
    Route::patch('divisions/{division}', 'AdminController@updateDivision');

    // edit handle
    Route::get('handles/{handle}/edit', 'AdminController@editHandle')->name('adminEditHandle');
    Route::put('handles/{handle}', 'AdminController@updateHandle')->name('adminUpdateHandle');
    Route::patch('handles/{handle}', 'AdminController@updateHandle');
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
