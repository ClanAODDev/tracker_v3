<?php

namespace App\Repositories;

use App\Models\Member;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClanRepository
{
    /**
     * @param  Division  $division
     * @return mixed
     */
    public function teamspeakReport()
    {
        return Member::whereHas('division')->get()
            ->filter(fn ($member) => ! carbon_date_or_null_if_zero($member->last_ts_activity));
    }

    /**
     * Get clan population totals groups by date ranges (typically weekly).
     *
     * @param  int  $limit
     * @return Collection|mixed
     */
    public function censusCounts($limit = 52)
    {
        return collect(DB::select("
            SELECT sum(count) as count, sum(weekly_active_count) as weekly_active, sum(weekly_voice_count) as weekly_voice_active, DATE_FORMAT(created_at, '%Y-%m-%d') as date
            FROM censuses 
            GROUP BY DATE(created_at)
            ORDER BY date DESC 
            LIMIT ?
        ", [$limit]));

    }

    /**
     * @return mixed
     */
    public function totalActiveMembers()
    {
        return DB::table('members')
            ->where('division_id', '!=', '0')->count();
    }

    /**
     * Breakdown of ranks across clan.
     *
     * @return array
     */
    public function allRankDemographic()
    {
        return DB::select('
            SELECT ranks.abbreviation, count(*) AS count
            FROM members
            JOIN ranks ON ranks.id = members.rank_id
            WHERE members.division_id != 0
            GROUP BY rank_id
            ORDER BY ranks.id ASC
        ');
    }
}
