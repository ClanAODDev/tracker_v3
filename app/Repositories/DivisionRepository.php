<?php

namespace App\Repositories;

use Carbon;
use DB;
use App\Division;

class DivisionRepository
{

    public function getDivisionActivity(Division $division)
    {
        $twoWeeksAgo = Carbon::now()->subDays(14);
        $oneMonthAgo = Carbon::now()->subDays(30);

        $twoWeeks = $division->activeMembers()
            ->where('last_activity', '>=', $twoWeeksAgo);

        $oneMonth = $division->activeMembers()
            ->where('last_activity', '<=', $twoWeeksAgo)
            ->where('last_activity', '>=', $oneMonthAgo);

        $moreThanOneMonth = $division->activeMembers()
            ->where('last_activity', '<=', $oneMonthAgo);

        return [
            'labels' => ['Less than 2 weeks', 'Less than 1 month', 'More than 1 month'],
            'values' => [$twoWeeks->count(), $oneMonth->count(), $moreThanOneMonth->count()],
            'colors' => ['#28b62c', '#ff851b', '#ff4136']
        ];
    }

    public function getRankDemographic(Division $division)
    {
        $ranks = DB::select(
            DB::raw("
               SELECT ranks.abbreviation, count(*) as count
               FROM members
               JOIN ranks ON ranks.id = members.rank_id
               JOIN division_member ON member_id = members.id
               WHERE division_id = {$division->id}
               GROUP BY rank_id
               ")
        );

        $labels = [];
        $values = [];

        foreach ($ranks as $rank) {
            $labels[] = $rank->abbreviation;
            $values[] = $rank->count;
        }

        $data = [
            'labels' => $labels,
            'values' => $values
        ];

        return $data;
    }

    public function withoutSsgts()
    {
        return Division::doesntHave('staffSergeants')->get();
    }
}