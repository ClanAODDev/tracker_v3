<?php

use App\Repositories\MemberRepository;
use Carbon\Carbon;

Auth::routes();

Route::post('/members/assign-squad', 'SquadController@assignMember');

/**
 * Application endpoints
 */
Route::get('/home', 'AppController@index')->name('home');
Route::get('/', 'AppController@index')->name('index');
Route::get('/impersonate-end/', 'ImpersonationController@endImpersonation')->name('end-impersonation');
Route::get('/impersonate/{user}', 'ImpersonationController@impersonate')->name('impersonate');

/**
 * ajax endpoints
 */
Route::get('search/members/{name?}', 'MemberController@search')->name('memberSearch');
Route::get('division-platoons/{abbreviation}', 'RecruitingController@searchPlatoons')->name('divisionPlatoons');
Route::post('division-tasks', 'RecruitingController@getTasks')->name('divisionTasks');
Route::post('search-member', 'MemberController@searchAutoComplete')->name('memberSearchAjax');
Route::post('platoon-squads', 'RecruitingController@searchPlatoonForSquads')->name('getPlatoonSquads');
Route::post('search-division-threads', 'RecruitingController@doThreadCheck')->name('divisionThreadCheck');
Route::post('update-role', 'UserController@updateRole');
Route::post('update-position', 'MemberController@updatePosition');
Route::post('update-handles', 'MemberController@updateHandles')->name('updateMemberHandles');

Route::group(['prefix' => 'inactive-members'], function () {
    Route::get('{member}/flag-inactive', 'InactiveMemberController@create')->name('member.flag-inactive');
    Route::get('{member}/unflag-inactive', 'InactiveMemberController@destroy')->name('member.unflag-inactive');
    Route::delete('{member}', 'InactiveMemberController@removeMember')->name('member.drop-for-inactivity');
});


// Members endpoints
Route::get('sergeants', 'MemberController@sergeants')->name('sergeants');

Route::group(['prefix' => 'members'], function () {
    Route::get('', 'MemberController@index')->name('members');

    Route::get('{member}', 'MemberController@show')->name('member');
    Route::get('{member}/edit-member', 'MemberController@edit')->name('editMember');
    Route::get('{member}/edit-user', 'UserController@edit')->name('editUser');
    Route::post('search/{name}', 'MemberController@search');
    Route::delete('{member}', 'MemberController@destroy')->name('deleteMember');

    // member leave
    Route::get('{member}/leave/{leave}/edit', 'LeaveController@edit')->name('leave.edit');
    Route::put('{member}/leave', 'LeaveController@update')->name('leave.update');
    Route::patch('{member}/leave', 'LeaveController@update');
    Route::delete('{member}/leave/{leave}', 'LeaveController@delete')->name('leave.delete');

    // member notes
    Route::group(['prefix' => '{member}/notes'], function () {
        Route::post('', 'NoteController@store')->name('storeNote');
        Route::get('{note}/edit', 'NoteController@edit')->name('editNote');
        Route::post('{note}', 'NoteController@update')->name('updateNote');
        Route::patch('{note}', 'NoteController@update');
        Route::delete('{note}', 'NoteController@delete')->name('deleteNote');
    });
});

Route::group(['prefix' => 'help'], function () {
    Route::get('/', 'HelpController@index')->name('help');
    Route::get('/division-structures', 'HelpController@divisionStructures')->name('divisionStructures');
});

Route::group(['prefix' => 'statistics'], function () {
    Route::get('/', 'ClanStatisticsController@show')->name('statistics');
    Route::get('/clan-ts-report', 'ClanStatisticsController@showTsReport')->name('clan.ts-report');
});

// initial recruiting screen
Route::get('recruit', 'RecruitingController@index')->name('recruiting.initial');
Route::post('add-member', 'RecruitingController@submitRecruitment')->name('recruiting.addMember');

Route::get('issues', 'IssuesController@index')->name('github.issues');
Route::post('issues', 'IssuesController@create')->name('github.create-issue');

/**
 * Division endpoints
 */
Route::group(['prefix' => 'divisions/'], function () {

    /**
     * divisions
     */
    Route::get('{division}', 'DivisionController@show')->name('division');
    Route::get('{division}/edit', 'DivisionController@edit')->name('editDivision');
    Route::get('{division}/census', 'DivisionController@census')->name('division.census');

    Route::post('', 'DivisionController@store')->name('storeDivision');
    Route::put('{division}', 'DivisionController@update')->name('updateDivision');
    Route::patch('{division}', 'DivisionController@update');
    Route::delete('{division}', 'DivisionController@destroy')->name('deleteDivision');

    Route::get('{division}/activity', 'ActivitiesController@byDivision')->name('divisionActivity');
    Route::get('{division}/part-timers', 'DivisionController@partTime')->name('partTimers');
    Route::get('{division}/part-timers/{member}', 'DivisionController@assignPartTime')->name('assignPartTimer');
    Route::get('{division}/part-timers/{member}/remove', 'DivisionController@removePartTime')->name('removePartTimer');
    Route::get('{division}/statistics', 'DivisionController@statistics')->name('divisionStats');

    Route::get('{division}/leave', 'LeaveController@index')->name('leave.index');
    Route::post('{division}/leave', 'LeaveController@store')->name('leave.store');

    Route::get('{division}/structure/edit',
        'DivisionStructureController@modify')->name('division.edit-structure');
    Route::get('{division}/structure', 'DivisionStructureController@show')->name('division.structure');
    Route::post('{division}/structure',
        'DivisionStructureController@update')->name('division.update-structure');

    Route::get('{division}/inactive-members/{platoon?}', 'InactiveMemberController@index')
        ->name('division.inactive-members');

    Route::get('{division}/promotions/{month?}/{year?}',
        function ($division, $month = null, $year = null, MemberRepository $memberRepository) {

            try {
                $dates = ($month && $year) ? [
                    Carbon::parse($month . " {$year}")->startOfMonth(),
                    Carbon::parse($month . " {$year}")->endOfMonth()
                ] : [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ];

                $members = $division->members()
                    ->with('rank')
                    ->whereBetween('last_promoted', $dates)
                    ->orderByDesc('rank_id')->get();
            } catch (Exception $exception) {
                $members = [];
            }

            $promotionPeriods = $memberRepository->promotionPeriods();

            return view('division.promotions', compact('members', 'division', 'promotionPeriods', 'year', 'month'));


        })->middleware(['auth'])->name('division.promotions');

    /**
     * Recruiting Process
     */
    Route::group(['prefix' => '{division}/recruit'], function () {
        Route::get('form', 'RecruitingController@form')->name('recruiting.form');
    });


    Route::get('{division}/ts-report', 'DivisionController@showTsReport')->name('division.ts-report');

    /**
     * platoons
     */
    Route::group(['prefix' => '{division}/platoons/'], function () {

        Route::get('/create', 'PlatoonController@create')->name('createPlatoon');
        Route::get('{platoon}', 'PlatoonController@show')->name('platoon');
        Route::get('{platoon}/edit', 'PlatoonController@edit')->name('editPlatoon');
        Route::get('{platoon}/squads', 'PlatoonController@squads')->name('platoonSquads');
        Route::get('{platoon}/manage', 'PlatoonController@manageSquads')->name('platoon.manage-squads');

        Route::post('', 'PlatoonController@store')->name('savePlatoon');
        Route::put('{platoon}', 'PlatoonController@update')->name('updatePlatoon');
        Route::patch('{platoon}', 'PlatoonController@update');
        Route::delete('{platoon}', 'PlatoonController@destroy')->name('deletePlatoon');


        /**
         * squads
         */
        Route::group(['prefix' => '{platoon}/squads/'], function () {
            Route::get('/create', 'SquadController@create')->name('createSquad');
            Route::get('{squad}/edit', 'SquadController@edit')->name('editSquad');

            Route::post('', 'SquadController@store')->name('storeSquad');
            Route::put('{squad}', 'SquadController@update')->name('updateSquad');
            Route::patch('{squad}', 'SquadController@update');
            Route::delete('{squad}', 'SquadController@destroy')->name('deleteSquad');
        });

    });
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

    Route::group(['prefix' => 'divisions'], function () {
        Route::post('', 'Admin\DivisionController@store')->name('adminStoreDivision');
        Route::get('{division}/edit', 'Admin\DivisionController@edit')->name('adminEditDivision');
        Route::get('create', 'Admin\DivisionController@create')->name('adminCreateDivision');
        Route::put('{division}', 'Admin\DivisionController@update')->name('adminUpdateDivision');
        Route::patch('{division}', 'Admin\DivisionController@update');
        Route::delete('{division}', 'Admin\DivisionController@destroy')->name('adminDeleteDivision');
    });

    // edit default tags
    Route::put('tags', 'Admin\TagController@update')->name('adminUpdateTags');
    Route::patch('tags', 'Admin\TagController@update');

    // edit handle
    Route::post('handles', 'Admin\HandleController@store')->name('adminStoreHandle');
    Route::get('handles/create', 'Admin\HandleController@create')->name('adminCreateHandle');
    Route::get('handles/{handle}/edit', 'Admin\HandleController@edit')->name('adminEditHandle');
    Route::put('handles/{handle}', 'Admin\HandleController@update')->name('adminUpdateHandle');
    Route::patch('handles/{handle}', 'Admin\HandleController@update');
    Route::delete('handles/{handle}', 'Admin\HandleController@delete')->name('adminDeleteHandle');
});


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
