<?php

/**
 * Admin / Member Request routes.
 */
Route::group(['prefix' => 'clan'], function () {
    Route::get('member-requests', 'Admin\MemberRequestController@index')
        ->name('admin.member-request.index');

    Route::get('/member-requests/{requestId}/reprocess', 'Admin\MemberRequestController@reprocess')
        ->name('admin.member-requests.reprocess');

    Route::get('member-requests/history', 'Admin\MemberRequestController@history')
        ->name('admin.member-request.history');

    Route::post('/member-requests/{requestId}/reprocess', 'Admin\MemberRequestController@reprocess')
        ->name('admin.member-requests.reprocess-confirm');

    Route::post('member-requests/{requestId}/approve', 'Admin\MemberRequestController@approve')
        ->name('admin.member-request.approve');

    Route::post('member-requests/{requestId}/cancel', 'Admin\MemberRequestController@cancel')
        ->name('admin.member-request.cancel');

    Route::post('member-requests/{requestId}/hold', 'Admin\MemberRequestController@placeOnHold')
        ->name('admin.member-request.place-on-hold');

    Route::post('member-requests/{requestId}/remove-hold', 'Admin\MemberRequestController@removeHold')
        ->name('admin.member-request.remove-hold');

    Route::post('member-requests/{memberRequest}/requeue', 'Admin\MemberRequestController@requeue')
        ->name('admin.member-request.requeue');

    Route::post('member-requests/{memberRequest}/name-change', 'Admin\MemberRequestController@handleNameChange')
        ->name('admin.member-request.name-change');

    Route::get('member-requests/{memberRequest}/validate', 'Admin\MemberRequestController@isAlreadyMember')
        ->name('admin.member-request.validate');

    Route::get('awards', 'AwardController@index')->name('awards.index');
    Route::get('awards/{award}', 'AwardController@show')->name('awards.show');
});
