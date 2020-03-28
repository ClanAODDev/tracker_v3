<?php

Route::group(['prefix' => 'members'], function () {
    // reset assignments
    Route::get('{member}/confirm-reset', 'MemberController@confirmUnassign')->name('member.confirm-reset');
    Route::post('{member}/unassign', 'MemberController@unassignMember')->name('member.unassign');
    Route::post('{member}/assign-platoon', 'MemberController@assignPlatoon')->name('member.assign-platoon');
    Route::post('assign-squad', 'SquadController@assignMember');


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


Route::group(['prefix' => 'inactive-members'], function () {
    Route::get('{member}/flag-inactive', 'InactiveMemberController@create')->name('member.flag-inactive');
    Route::get('{member}/unflag-inactive', 'InactiveMemberController@destroy')->name('member.unflag-inactive');
    Route::delete('{member}', 'InactiveMemberController@removeMember')->name('member.drop-for-inactivity');
});

// initial recruiting screen

