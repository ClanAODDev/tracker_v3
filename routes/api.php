<?php

use App\Notifications\ITTeamAlert;
use Illuminate\Support\Facades\Notification;

Route::prefix('v1')->group(function () {
    Route::middleware(['abilities:clan:read'])->group(function () {
        Route::get('ts-count', 'API\v1\ClanController@teamspeakPopulationCount')->name('v1.ts_population');
        Route::get('discord-count', 'API\v1\ClanController@discordPopulationCount')->name('v1.discord_population');
        Route::get('stream-events', 'API\v1\ClanController@streamEvents')->name('v1.stream_events');
    });

    Route::middleware(['abilities:division:read'])->group(function () {
        Route::get('divisions', 'API\v1\DivisionController@index')->name('v1.divisions.index');
        Route::get('divisions/{slug}', 'API\v1\DivisionController@show')->name('v1.divisions.show');
    });

    Route::middleware(['abilities:division:write'])->group(function () {
        Route::post('divisions/{slug}', 'API\v1\DivisionController@update')->name('v1.divisions.update');
    });



});

Route::post('make-job', function () {
    Notification::send(sprintf(
        "%s/channel/%s",
        config('app.aod.bot_api_base_url'),
        861037177659064330
    ), new ITTeamAlert(
        'A queued job ran',
        'It was a success!'
    ));
});


// basic
// squads
// platoons
// members
// division-level only
// msgt+ gets clan-wide
