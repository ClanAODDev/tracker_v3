<?php

/**
 * Application endpoints.
 */
Route::get('/home', 'AppController@index')->name('home');
Route::get('/', 'AppController@index')->name('index');
Route::get('/impersonate-end/', 'ImpersonationController@endImpersonation')->name('end-impersonation');
Route::get('/impersonate/{user}', 'ImpersonationController@impersonate')->name('impersonate');

Route::get('/settings', 'UserSettingsController@show')->name('user.settings.show');
Route::patch('/settings', 'UserSettingsController@update')->name('user.settings.update');

Route::group(['prefix' => 'training'], function () {
    Route::get('sgt', 'TrainingController@sgtTraining')->name('training.sgt');
    Route::get('ssgt', 'TrainingController@index')->name('training.ssgt');
    Route::get('msgt', 'TrainingController@index')->name('training.msgt');
    Route::post('', 'TrainingController@update')->name('training.update');
});

Route::get('developers', 'DeveloperController@index')->name('developer');
Route::post('developers/tokens', 'DeveloperController@generateToken')->name('developer.token.store');
Route::delete('developers/tokens', 'DeveloperController@destroyToken')->name('developer.token.delete');

/*
 * Application UI.
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
 * Discord command handler.
 */
Route::get('bot/commands/{command}', 'Bot\BotCommandController@index')->name('bot.commands')->middleware('bot');

// force admin login to use existing auth
Route::get('/admin/login', fn () => redirect('login'))->name('filament.admin.auth.login');
