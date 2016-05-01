<?php

namespace App\AOD\Division;

class Preferences
{
    public static function ActivityThreshold()
    {
        // TODO: fetch from division settings
        return [
            [
                'days' => 30,
                'class' => 'text-danger',
                'color' => 'red'
            ],

            [
                'days' => 14,
                'class' => 'text-warning',
                'color' => 'orange'
            ],

            [
                'days' => 0,
                'class' => 'text-success',
                'color' => 'green'
            ],
        ];
    }

}