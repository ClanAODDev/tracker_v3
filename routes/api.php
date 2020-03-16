<?php

Route::group(
    [
        'prefix' => 'v1',

        'middleware' => [
            'auth:api',
            'throttle:30',
            'scope:query-division-info-basic'
        ]

    ],
    function () {
        Route::get('divisions', 'API\v1\DivisionController@index');
        Route::get('divisions/{abbreviation}', 'API\v1\DivisionController@show');
        Route::get('ts-count', 'API\v1\ClanController@teamspeakPopulationCount');
        Route::get('discord-count', 'API\v1\ClanController@discordPopulationCount');
        Route::get('stream-events', 'API\v1\ClanController@streamEvents');
    }
);
