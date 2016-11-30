<?php

namespace App\Division;

class Preferences
{

    public static function ActivityThreshold()
    {
        // TODO: fetch from division settings
        return [
            [
                'days' => 30,
                'class' => 'text-danger',
            ],

            [
                'days' => 14,
                'class' => 'text-warning',
            ],

            [
                'days' => 0,
                'class' => 'text-success',
            ],
        ];
    }
}
