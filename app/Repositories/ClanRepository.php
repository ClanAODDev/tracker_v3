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
    public function censusCounts($limit = 52)
    {
        $censuses = collect(DB::select(
            DB::raw("    
                SELECT sum(count) as count, sum(weekly_active_count) as weekly_active, date_format(created_at,'%y-%m-%d') as date 
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
     * @param $rank
     * @return mixed
     * @throws \Exception
     */
    public function rankDemographic($rank)
    {
        if ( ! is_int($rank) && ! is_array($rank)) {
            throw new \Exception('Rank provided to rank demographic must be an integer or an array');
        }

        if (is_array($rank)) {
            $rankIds = implode(',', $rank);
            $where = "WHERE rank_id IN ({$rankIds})";
        } else {
            $where = "WHERE rank_id = {$rank}";
        }

        $data = collect(DB::select(
            DB::raw("
                SELECT count(*) as count FROM members
                INNER JOIN division_member
                ON member_id = members.id
                {$where}
                AND division_member.primary = 1
            ")
        ))->first();

        return $data->count;
    }

    /**
     * Breakdown of ranks across clan
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
