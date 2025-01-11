<?php

Route::name('help.tickets.')->prefix('help/tickets')->group(function () {
    Route::get('', 'TicketController@index')->name('index');
    Route::get('setup', 'TicketController@setup')->name('setup');
    Route::get('create', 'TicketController@create')->name('create');
    Route::post('', 'TicketController@store')->name('store');
    Route::delete('', 'TicketController@store')->name('delete');
    Route::get('{ticket}', 'TicketController@show')->name('show');
    Route::patch('{ticket}/self-assign', 'TicketController@selfAssign')->name('self-assign');
    Route::patch('{ticket}/assign-to', 'TicketController@assignTo')->name('assign-to');
    Route::patch('{ticket}/resolve', 'TicketController@resolve')->name('resolve');
    Route::patch('{ticket}/reopen', 'TicketController@reopen')->name('reopen');
    Route::patch('{ticket}/reject', 'TicketController@reject')->name('reject');

    Route::name('comments.')->prefix('{ticket}/comments')->group(function () {
        Route::post('', 'TicketCommentController@store')->name('store');
        Route::delete('', 'TicketCommentController@delete')->name('delete');
    });
});
