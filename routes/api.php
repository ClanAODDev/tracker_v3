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
        // Basic information on divisions
        Route::get('divisions', 'API\v1\DivisionController@index');

        // Basic information about a specific division
        Route::get('divisions/{abbreviation}', 'API\v1\DivisionController@show');
    }
);
