<?php

/**
 * Application endpoints.
 */
Route::get('/home', 'AppController@index')->name('home');
Route::get('/', 'AppController@index')->name('index');
Route::get('/impersonate-end/', 'ImpersonationController@endImpersonation')->name('end-impersonation');
Route::get('/impersonate/{user}', 'ImpersonationController@impersonate')->name('impersonate');

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

Route::post('settings', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    $user->settings = array_merge($user->settings, [
        'disable_animations' => filter_var($request->input('disable_animations'), FILTER_VALIDATE_BOOLEAN),
        'mobile_nav_side' => $request->input('mobile_nav_side', 'right'),
        'snow' => $request->input('snow', 'no_snow'),
        'ticket_notifications' => filter_var($request->input('ticket_notifications'), FILTER_VALIDATE_BOOLEAN),
    ]);
    $user->save();

    return response()->json(['success' => true]);
})->middleware('auth')->name('settings.update');

Route::post('settings/part-time-divisions', function (\Illuminate\Http\Request $request) {
    $member = auth()->user()->member;
    if (! $member) {
        return response()->json(['error' => 'No member record'], 400);
    }

    $divisionIds = $request->input('divisions', []);
    $activeIds = \App\Models\Division::active()->pluck('id')->all();
    $validIds = array_values(array_intersect($divisionIds, $activeIds));
    $member->partTimeDivisions()->sync($validIds);

    return response()->json(['success' => true, 'count' => count($validIds)]);
})->middleware('auth')->name('settings.part-time-divisions');

Route::post('settings/ingame-handles', function (\Illuminate\Http\Request $request) {
    $member = auth()->user()->member;
    if (! $member) {
        return response()->json(['error' => 'No member record'], 400);
    }

    $handles = $request->input('handles', []);
    \App\Filament\Forms\Components\IngameHandlesForm::saveHandles($member, $handles);

    return response()->json(['success' => true, 'count' => $member->memberHandles()->count()]);
})->middleware('auth')->name('settings.ingame-handles');

/*
 * Discord command handler.
 */
Route::get('bot/commands/{command}', 'Bot\BotCommandController@index')->name('bot.commands')->middleware('bot');

// force admin login to use existing auth
Route::get('/admin/login', fn () => redirect('login'))->name('filament.admin.auth.login');
