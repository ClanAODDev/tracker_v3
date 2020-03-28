<?php

/**
 * Admin / Member Request routes
 */
Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::get('member-requests', 'Admin\MemberRequestController@index')->name('admin.member-request.index');
    Route::get('/member-requests/{requestId}/reprocess', 'Admin\MemberRequestController@reprocess')
        ->name('admin.member-requests.reprocess');
    Route::post('/member-requests/{requestId}/reprocess', 'Admin\MemberRequestController@reprocess')
        ->name('admin.member-requests.reprocess-confirm');
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