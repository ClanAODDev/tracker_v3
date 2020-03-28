<?php

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
