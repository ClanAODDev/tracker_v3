<?php

Route::prefix('v1')->group(
    function () {
        Route::get(
            'divisions',
            'API\v1\DivisionController@index'
        )->name('v1.divisions.index');

        Route::get(
            'divisions/{abbreviation}',
            'API\v1\DivisionController@show'
        )->name('v1.divisions.show');

        Route::get(
            'ts-count',
            'API\v1\ClanController@teamspeakPopulationCount'
        )->name('v1.ts_population');

        Route::get(
            'discord-count',
            'API\v1\ClanController@discordPopulationCount'
        )->name('v1.discord_population');

        Route::get(
            'stream-events',
            'API\v1\ClanController@streamEvents'
        )->name('v1.stream_events');
    }
);


// basic
// squads
// platoons
// members
// division-level only
// msgt+ gets clan-wide
