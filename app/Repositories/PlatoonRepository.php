<?php

namespace App\Repositories;

use App\Division;
use App\Member;
use App\Platoon;
use Carbon;

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
        $twoWeeksAgo = Carbon::now()->subDays(14);
        $oneMonthAgo = Carbon::now()->subDays(30);

        $twoWeeks = $platoon->members()->where('last_activity', '>=', $twoWeeksAgo);

        $oneMonth = $platoon->members()->where('last_activity', '<=', $twoWeeksAgo)
            ->where('last_activity', '>=', $oneMonthAgo);

        $moreThanOneMonth = $platoon->members()->where('last_activity', '<=', $oneMonthAgo);

        return [
            'labels' => ['Less than 2 weeks', 'Less than 1 month', 'More than 1 month'],
            'values' => [$twoWeeks->count(), $oneMonth->count(), $moreThanOneMonth->count()],
            'colors' => ['#28b62c', '#ff851b', '#ff4136']
        ];
    }
}
