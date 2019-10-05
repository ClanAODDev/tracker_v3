<?php

include_once('extra/requests.php');

Auth::routes();

Route::get('logout', 'Auth\LoginController@logout')->name('logout');

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
Route::post('validate-id/{memberId}', 'RecruitingController@validateMemberId')
    ->name('validate-id');
Route::post('validate-name', 'RecruitingController@validateMemberName')->name('validate-name');
Route::post('division-tasks', 'RecruitingController@getTasks')->name('divisionTasks');
Route::post('search-member', 'MemberController@searchAutoComplete')->name('memberSearchAjax');
Route::post('platoon-squads', 'RecruitingController@searchPlatoonForSquads')->name('getPlatoonSquads');
Route::post('search-division-threads', 'RecruitingController@doThreadCheck')->name('divisionThreadCheck');
Route::post('update-role', 'UserController@updateRole');
Route::post('update-position', 'MemberController@updatePosition')->name('member.position.update');
Route::post('update-handles', 'MemberController@updateHandles')->name('updateMemberHandles');

Route::group(['prefix' => 'inactive-members'], function () {
    Route::get('{member}/flag-inactive', 'InactiveMemberController@create')->name('member.flag-inactive');
    Route::get('{member}/unflag-inactive', 'InactiveMemberController@destroy')->name('member.unflag-inactive');
    Route::delete('{member}', 'InactiveMemberController@removeMember')->name('member.drop-for-inactivity');
});


// Members endpoints
Route::get('sergeants', 'MemberController@sergeants')->name('sergeants');

Route::group(['prefix' => 'members'], function () {
    // reset assignments
    Route::get('{member}/confirm-reset', 'MemberController@confirmUnassign')->name('member.confirm-reset');
    Route::post('{member}/unassign', 'MemberController@unassignMember')->name('member.unassign');
    Route::post('{member}/assign-platoon', 'MemberController@assignPlatoon')->name('member.assign-platoon');


    Route::get('{member}/edit-member', 'MemberController@edit')->name('editMember');
    Route::get('{member}/edit-user', 'UserController@edit')->name('editUser');
    Route::get('{member}/edit-part-time', 'MemberController@editPartTime')->name('member.edit-part-time');
    Route::get('{member}/edit-handles', 'MemberController@editHandles')->name('member.edit-handles');
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

    Route::get('{member}-{slug?}', 'MemberController@show')->name('member');
});


Route::group(['prefix' => 'help'], function () {
    Route::get('/', 'HelpController@index')->name('help');
    Route::get('/division-structures', 'HelpController@divisionStructures')->name('divisionStructures');
});

// initial recruiting screen
Route::get('recruit', 'RecruitingController@index')->name('recruiting.initial');
Route::post('add-member', 'RecruitingController@submitRecruitment')->name('recruiting.addMember');

Route::get('issues', 'IssuesController@index')->name('github.issues');
Route::post('issues', 'IssuesController@create')->name('github.create-issue');

Route::get('changelog', 'AppController@changelog')->name('changelog');

/**
 * Division endpoints
 */
Route::group(['prefix' => 'divisions/{division}'], function () {

    /**
     * division
     */
    Route::get('/', 'DivisionController@show')->name('division');
    Route::get('/edit', 'DivisionController@edit')->name('editDivision');

    Route::post('/', 'DivisionController@store')->name('storeDivision');
    Route::put('/', 'DivisionController@update')->name('updateDivision');
    Route::patch('/', 'DivisionController@update');
    Route::delete('/', 'DivisionController@destroy')->name('deleteDivision');

    Route::get('/activity', 'ActivitiesController@byDivision')->name('divisionActivity');
    Route::get('/part-timers', 'DivisionController@partTime')->name('partTimers');
    Route::get('/part-timers/{member}', 'DivisionController@assignPartTime')->name('assignPartTimer');
    Route::get('/part-timers/{member}/remove', 'DivisionController@removePartTime')->name('removePartTimer');
    Route::get('/statistics', 'DivisionController@statistics')->name('divisionStats');

    Route::get('/leave', 'LeaveController@index')->name('leave.index');
    Route::post('/leave', 'LeaveController@store')->name('leave.store');

    Route::get('/structure/edit', 'DivisionStructureController@modify')->name('division.edit-structure');
    Route::get('/structure', 'DivisionStructureController@show')->name('division.structure');
    Route::post('/structure', 'DivisionStructureController@update')->name('division.update-structure');

    Route::get('/inactive-members/{platoon?}', 'InactiveMemberController@index')
        ->name('division.inactive-members');
    Route::get('/inactive-members-ts/{platoon?}', 'InactiveMemberController@index')
        ->name('division.inactive-members-ts');

    Route::get('/members', 'DivisionController@members')->name('division.members');

    Route::get('/notes', 'DivisionNoteController@index')->name('division.notes');


    /**
     * Member requests
     */
    Route::get('/member-requests', 'Division\MemberRequestController@index')
        ->name('division.member-requests.index');
    Route::get('/member-requests/{memberRequest}/edit', 'Division\MemberRequestController@edit')
        ->name('division.member-requests.edit');
    Route::post('/member-requests/{memberRequest}/cancel', 'Division\MemberRequestController@cancel')
        ->name('division.member-requests.cancel');
    Route::patch('/member-requests/{memberRequest}', 'Division\MemberRequestController@update')
        ->name('division.member-requests.update');
    Route::delete('/member-requests/{memberRequest}/delete', 'Division\MemberRequestController@destroy')
        ->name('division.member-requests.delete');

    /**
     * Recruiting Process
     */
    Route::group(['prefix' => '/recruit'], function () {
        Route::get('form', 'RecruitingController@form')->name('recruiting.form');
    });

    /**
     * Division Reports
     */
    Route::get('/ts-report', 'Division\ReportController@tsReport')
        ->name('division.ts-report');
    Route::get('/retention', 'Division\ReportController@retentionReport')
        ->name('division.retention-report');
    Route::get('/census', 'Division\ReportController@censusReport')
        ->name('division.census');
    Route::get(
        '/promotions/{month?}/{year?}',
        'Division\ReportController@promotionsReport'
    )->middleware(['auth'])
        ->name('division.promotions');
    Route::get(
        '/ingame-report/{customAttr?}',
        'Division\ReportController@ingameReport'
    )->middleware(['auth'])
        ->name('division.ingame-reports');

    /**
     * member requests
     */
//    Route::

    /**
     * platoons
     */
    Route::group(['prefix' => '/platoons/'], function () {
        Route::get('/create', 'PlatoonController@create')->name('createPlatoon');
        Route::get('{platoon}', 'PlatoonController@show')->name('platoon');
        Route::get('{platoon}/edit', 'PlatoonController@edit')->name('editPlatoon');
        Route::get('{platoon}/manage', 'PlatoonController@manageSquads')->name('platoon.manage-squads');
        Route::get('{platoon}/csv', 'PlatoonController@exportAsCsv')->name('platoon.export-csv');

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
            Route::get('{squad}', 'SquadController@show')->name('squad.show');

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


Route::group(['prefix' => 'slack'], function () {
    Route::group(['prefix' => '/users'], function () {
        Route::get('', 'Slack\SlackUserController@index')->name('slack.user-index');
    });

    Route::group(['prefix' => '/files'], function () {
        Route::get('', 'Slack\SlackFilesController@index')->name('slack.files');
        Route::get('/purge', 'Slack\SlackFilesController@purgeAll')->name('slack.files.purge');
        Route::get('/{fileId}/delete', 'Slack\SlackFilesController@destroy')->name('slack.files.delete');
    });

    Route::group(['prefix' => '/channels'], function () {
        Route::get('', 'Slack\SlackChannelController@index')
            ->name('slack.channel-index');
        Route::post('', 'Slack\SlackChannelController@store')->name('slack.store-channel');
        Route::get('/confirm-archive/{channel}', 'Slack\SlackChannelController@confirmArchive')
            ->name('slack.confirm-archive-channel');
        Route::get('/unarchive/{channel}', 'Slack\SlackChannelController@unarchive')
            ->name('slack.unarchive-channel');
        Route::post('/archive', 'Slack\SlackChannelController@archive')->name('slack.archive-channel');
    });
});

/**
 * Slack handler
 */
Route::post('slack', [
    'as' => 'slack.commands',
    'uses' => 'Slack\SlackCommandController@index',
])->middleware('slack');

/** Reports */
Route::group(['prefix' => 'reports'], function () {
    Route::middleware('admin')->get('division-turnover', 'ReportsController@divisionTurnoverReport')
        ->name('reports.division-turnover');

    Route::middleware('admin')->get('division-roles', 'ReportsController@divisionUsersWithAccess')
        ->name('reports.division-roles');

    Route::get('no-discord', 'ReportsController@usersWithoutDiscordReport')
        ->name('reports.discord');
    Route::get(
        'outstanding-inactives',
        'ReportsController@outstandingMembersReport'
    )->name('reports.outstanding-inactives');
    Route::get('/clan-census', 'ReportsController@clanCensusReport')->name('reports.clan-census');
    Route::get('/clan-ts-report', 'ReportsController@clanTsReport')->name('reports.clan-ts-report');
});

Route::group(['prefix' => 'training'], function () {
    Route::get('', 'TrainingController@index')->name('training.index');
});


/**
 * Admin / Member Request routes
 */
Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::get('tickets', 'Admin\TicketController@index')->name('admin.tickets');


    Route::get('member-requests', 'Admin\MemberRequestController@index')->name('admin.member-request.index');
    Route::post(
        'member-requests/{requestId}/approve',
        'Admin\MemberRequestController@approve'
    )->name('admin.member-request.approve');

    Route::post('member-requests/{requestId}/cancel', 'Admin\MemberRequestController@cancel')
        ->name('admin.member-request.cancel');

    Route::post('member-requests/{memberRequest}/requeue', 'Admin\MemberRequestController@requeue')
        ->name('admin.member-request.requeue');

    Route::post('member-requests/{memberRequest}/name-change', 'Admin\MemberRequestController@handleNameChange')
        ->name('admin.member-request.name-change');

    Route::get('member-requests/{memberRequest}/validate', 'Admin\MemberRequestController@isAlreadyMember')
        ->name('admin.member-request.validate');
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
