<?php

namespace App\Repositories;

use DB;
use App\Division;

class DivisionRepository
{
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

        $data = [];

        foreach ($ranks as $rank) {
            $data[] = [
                'label' => $rank->abbreviation,
                'value' => $rank->count
            ];
        }

        return $data;
    }
}