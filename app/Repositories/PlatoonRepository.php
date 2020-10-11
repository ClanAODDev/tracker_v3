<?php

namespace App\Repositories;

use App\Models\Platoon;
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
    public function getPlatoonForumActivity(Platoon $platoon)
    {
        $twoWeeksAgo = Carbon::now()->subDays(14);
        $oneMonthAgo = Carbon::now()->subDays(30);

        $twoWeeks = $platoon->members()->where('last_activity', '>=', $twoWeeksAgo)->count();

        $oneMonth = $platoon->members()->where('last_activity', '<=', $twoWeeksAgo)
            ->where('last_activity', '>=', $oneMonthAgo)->count();

        $moreThanOneMonth = $platoon->members()->where('last_activity', '<=', $oneMonthAgo)->count();

        return [
            'labels' => ['Current', '14 days', '30 days'],
            'values' => [$twoWeeks, $oneMonth, $moreThanOneMonth],
            'colors' => ['#28b62c', '#ff851b', '#ff4136']
        ];
    }

    public function getPlatoonTSActivity(Platoon $platoon)
    {
        $twoWeeksAgo = Carbon::now()->subDays(14);
        $oneMonthAgo = Carbon::now()->subDays(30);

        $twoWeeks = $platoon->members()->where('last_ts_activity', '>=', $twoWeeksAgo)->count();

        $oneMonth = $platoon->members()->where('last_ts_activity', '<=', $twoWeeksAgo)
            ->where('last_ts_activity', '>=', $oneMonthAgo)->count();

        $moreThanOneMonth = $platoon->members()->where('last_ts_activity', '<=', $oneMonthAgo)->count();

        return [
            'labels' => ['Current', '14 days', '30 days'],
            'values' => [$twoWeeks, $oneMonth, $moreThanOneMonth],
            'colors' => ['#28b62c', '#ff851b', '#ff4136']
        ];
    }
}
