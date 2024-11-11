<?php

namespace App\Repositories;

use App\Enums\Rank;
use App\Models\Member;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClanRepository
{
    /**
     * Get clan population totals groups by date ranges (typically weekly).
     *
     * @param  int  $limit
     * @return Collection|mixed
     */
    public function censusCounts($limit = 52)
    {
        $query = "
            SELECT 
                SUM(count) AS count, 
                SUM(weekly_active_count) AS weekly_active, 
                SUM(weekly_voice_count) AS weekly_voice_active, 
                DATE_FORMAT(created_at, '%Y-%m-%d') AS date
            FROM 
                censuses
            GROUP BY 
                DATE(created_at)
            ORDER BY 
                date DESC
            LIMIT :limit
        ";

        $results = DB::select($query, ['limit' => $limit]);

        return collect($results);

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
     */
    public function allRankDemographic()
    {
        $results = DB::select('
            SELECT members.rank, count(*) AS count
            FROM members
            WHERE members.division_id != 0
            GROUP BY rank
            ORDER BY rank ASC
        ');

        return collect($results)->map(function ($row) {
            $rank = Rank::from($row->rank);

            return (object) [
                'abbreviation' => $rank->getAbbreviation(),
                'count' => $row->count,
            ];
        });
    }
}
