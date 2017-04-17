<?php

namespace App\Repositories;

use App\Platoon;
use Carbon\Carbon;

class PlatoonRepository
{
    /**
     * @param Platoon $platoon
     * @return array
     *
     * Returns activity data for platoon members based on last_activity
     * @TODO: Allow divisions to manage this setting
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
            'labels' => ['Current', '14 days', '30 days'],
            'values' => [$twoWeeks->count(), $oneMonth->count(), $moreThanOneMonth->count()],
            'colors' => ['#28b62c', '#ff851b', '#ff4136']
        ];
    }
}
