<?php

namespace App\Repositories;

use App\Platoon;

class PlatoonRepository
{
    /**
     * @param Platoon $platoon
     * @return array
     *
     * Returns activity data for platoon members based on last_forum_login
     */
    public function getPlatoonActivity(Platoon $platoon)
    {
        $twoWeeks = $platoon->members()->whereRaw('last_forum_login BETWEEN DATE_ADD(CURDATE(), INTERVAL -14 DAY) AND CURDATE()')->count();
        $oneMonth = $platoon->members()->whereRaw('last_forum_login BETWEEN DATE_ADD(CURDATE(), INTERVAL -30 DAY) AND DATE_ADD(CURDATE(), INTERVAL -14 DAY)')->count();
        $moreThanOneMonth = $platoon->members()->whereRaw('last_forum_login < DATE_ADD(CURDATE(), INTERVAL -30 DAY)')->count();

        return [
            'labels' => ['< 2 weeks', '< 1 month', '> 1 month'],
            'values' => [$twoWeeks, $oneMonth, $moreThanOneMonth],
            'colors' => ['#28b62c', '#ff851b', '#ff4136']
        ];
    }
}