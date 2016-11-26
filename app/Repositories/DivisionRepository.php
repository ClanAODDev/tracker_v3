<?php

namespace App\Repositories;

use DB;
use App\Division;

class DivisionRepository
{

    public function getDivisionActivity(Division $division)
    {
        $twoWeeks = $division->members()->whereRaw('last_forum_login BETWEEN DATE_ADD(CURDATE(), INTERVAL -14 DAY) AND CURDATE()')->count();
        $oneMonth = $division->members()->whereRaw('last_forum_login BETWEEN DATE_ADD(CURDATE(), INTERVAL -30 DAY) AND DATE_ADD(CURDATE(), INTERVAL -14 DAY)')->count();
        $moreThanOneMonth = $division->members()->whereRaw('last_forum_login < DATE_ADD(CURDATE(), INTERVAL -30 DAY)')->count();

        return [
            'labels' => ['Less than 2 weeks', 'Less than 1 month', 'More than 1 month'],
            'values' => [$twoWeeks, $oneMonth, $moreThanOneMonth],
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