<?php

namespace App\Repositories;

use App\Models\Member;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClanRepository
{

    /**
     * @param Division $division
     * @return mixed
     */
    public function teamspeakReport()
    {
        return Member::whereHas('division')->get()
            ->filter(function ($member) {
                return !carbon_date_or_null_if_zero($member->last_ts_activity);
            });
    }

    /**
     * Get clan population totals groups by date ranges (typically weekly)
     *
     * @param int $limit
     * @return Collection|mixed
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
        $members = DB::table('members')
            ->where('division_id', '!=', '0')->count();

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
                SELECT ranks.abbreviation, count(*) AS count
                FROM members
                  JOIN ranks
                    ON ranks.id = members.rank_id
                WHERE members.division_id != 0
                GROUP BY rank_id
                ORDER BY ranks.id ASC 
            ")
        );
    }
}
