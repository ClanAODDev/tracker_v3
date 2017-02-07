<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ClanRepository
{

    /**
     * Get clan population totals groups by date ranges (typically weekly)
     * @param int $limit
     * @return \Illuminate\Support\Collection|mixed
     */
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

    /**
     * @return mixed
     */
    public function totalActiveMembers()
    {
        $members = DB::table('division_member')
            ->where('primary', '1')->count();

        return $members;
    }

    /**
     * Measure recruit count
     *
     * @return mixed
     */
    public function recruitDemographic()
    {
        $data = collect(DB::select(
            DB::raw("
                SELECT count(*) as count FROM members
                INNER JOIN division_member
                ON member_id = members.id
                WHERE rank_id = 1
                AND division_member.primary = 1
            ")
        ))->first();

        return $data->count;
    }

    /**
     * Breakdown of ranks across clan
     * @return array
     */
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