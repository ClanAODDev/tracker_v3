<?php

use League\Csv\Writer;

Auth::routes();

Route::get('/home', 'AppController@index');
Route::get('/', 'AppController@index');

//Route::get('send/mail', 'UserController@sendEmailReminder');

// members
Route::get('members/{member}', 'MemberController@show')->name('member');
Route::get('members/{member}/edit', 'MemberController@edit')->name('editMember');
Route::get('search/members/{name}', 'MemberController@search')->name('memberSearch');

// member data handling
Route::delete('members/{member}/delete', 'MemberController@destroy')->name('deleteMember');


/**
 * Division Endpoints
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
    Route::get('{division}/platoons/{platoon}/squads/create', 'SquadController@show')->name('createSquad');
    Route::get('{division}/squads/{squad}', 'SquadController@show')->name('squad');
});


// logging activity
Route::get('users/{username}/activity', 'ActivitiesController@byUser');


/**
 * Vue endpoints
 */
Route::group(['prefix' => 'v1/api'], function () {
    Route::get('activity/platoon/{platoon}', 'PlatoonController@activity');
    Route::get('stats/ranks/division/{division}', 'DivisionController@rankDemographic');
});


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
 * AOD Forum sync endpoint
 */
Route::group(['prefix' => 'AOD', 'middleware' => 'throttle:15'], function () {
    Route::get('/division-data/{division_name}', function ($division_name) {
        $info = new \App\AOD\MemberSync\GetDivisionInfo($division_name);

        return response()->json($info);
    });
});


Route::group(['prefix' => 'v1/api', 'middleware' => 'throttle:30'], function () {
    Route::get('members', 'API\APIController@members');
    Route::get('users', 'API\APIController@users');
    Route::get('divisions', 'API\APIController@divisions');
    Route::get('squads', 'API\APIController@squads');
    Route::get('platoons', 'API\APIController@platoons');
});

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
   Route::get('/', 'AdminController@index')->name('admin');
   Route::patch('divisions/update', 'AdminController@updateDivisions')->name('updateDivisions');
});

Route::get('7dayactive', function() {
    $division = \App\Division::find(4);
    $members = $division->membersActiveSinceDaysAgo(8)->get();
    $csv = Writer::createFromFileObject(new \SplTempFileObject());
    $csv->insertOne(\Schema::getColumnListing('members'));


    foreach ($members as $person) {
        $csv->insertOne($person->toArray());
    }

    $csv->output('7dayactive.csv');
});
