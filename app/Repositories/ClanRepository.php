<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ClanRepository
{

    /**
     * Get clan population totals groups by date ranges (typically weekly)
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection|mixed
     */
    public function censusCounts($limit = 52)
    {
        $censuses = collect(DB::select(
            DB::raw("    
                SELECT sum(count) as count, sum(weekly_active_count) as weekly_active, date_format(created_at,'%y-%m-%d') as date 
                FROM censuses GROUP BY date(created_at) 
                ORDER BY date DESC LIMIT {$limit};
            ")
        ));

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
     * Breakdown of ranks across clan
     *
     * @return array
     */
    public function allRankDemographic()
    {
        return DB::select(
            DB::raw("
                SELECT
                  ranks.abbreviation,
                  count(*) AS count
                FROM members
                  JOIN ranks
                    ON ranks.id = members.rank_id
                  JOIN division_member
                    ON member_id = members.id
                WHERE division_member.primary = 1
                GROUP BY rank_id
                ORDER BY ranks.id ASC 
            ")
        );
    }
}
