<?php

namespace App\Repositories;

use App\Division;
use App\Member;
use App\Platoon;

class PlatoonRepository
{
    /**
     * @param Platoon $platoon
     * @return array
     *
     * Returns activity data for platoon members based on last_activity
     */
    public function getPlatoonActivity(Platoon $platoon)
    {
        $twoWeeks = $platoon->members()->whereRaw('last_activity BETWEEN DATE_ADD(CURDATE(), INTERVAL -14 DAY) AND CURDATE()')->count();
        $oneMonth = $platoon->members()->whereRaw('last_activity BETWEEN DATE_ADD(CURDATE(), INTERVAL -30 DAY) AND DATE_ADD(CURDATE(), INTERVAL -14 DAY)')->count();
        $moreThanOneMonth = $platoon->members()->whereRaw('last_activity < DATE_ADD(CURDATE(), INTERVAL -30 DAY)')->count();

        return [
            'labels' => ['< 2 weeks', '< 1 month', '> 1 month'],
            'values' => [$twoWeeks, $oneMonth, $moreThanOneMonth],
            'colors' => ['#28b62c', '#ff851b', '#ff4136']
        ];
    }
}