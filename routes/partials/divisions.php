<?php

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
