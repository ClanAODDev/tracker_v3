<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ClanRepository
{

    public function censusCounts($limit = 10)
    {
        $censuses = collect(DB::select(
            DB::raw("    
                SELECT sum(count) as count, date_format(created_at,'%y-%m-%d') as date 
                FROM censuses GROUP BY date(created_at) 
                ORDER BY date DESC LIMIT {$limit};
            ")
        ));

        if ($limit === 1) {
            return $censuses->first();
        }

        return $censuses;
    }

    public function totalActiveMembers()
    {
        $members = DB::table('division_member')->where('primary', '1')->count();

        return $members;
    }

    public function rankDemographic()
    {
        $ranks = DB::select(
            DB::raw("
               SELECT ranks.abbreviation, count(*) as count
               FROM members
               JOIN ranks ON ranks.id = members.rank_id
               JOIN division_member ON member_id = members.id
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
}