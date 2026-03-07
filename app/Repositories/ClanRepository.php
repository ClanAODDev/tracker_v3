<?php

namespace App\Repositories;

use App\Enums\Rank;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClanRepository
{
    private function deduplicatedCensusQuery(string $whereClause = ''): string
    {
        return "
            SELECT
                SUM(c.count) AS count,
                SUM(c.weekly_active_count) AS weekly_active,
                SUM(c.weekly_voice_count) AS weekly_voice_active,
                DATE_FORMAT(c.created_at, '%Y-%m-%d') AS date
            FROM censuses c
            INNER JOIN (
                SELECT MAX(id) AS id
                FROM censuses
                {$whereClause}
                GROUP BY division_id, DATE(created_at)
            ) dedup ON c.id = dedup.id
            GROUP BY DATE(c.created_at)
        ";
    }

    /**
     * @param  int  $limit
     * @return Collection|mixed
     */
    public function censusCounts($limit = 52)
    {
        $query = $this->deduplicatedCensusQuery() . '
            ORDER BY date DESC
            LIMIT :limit
        ';

        return collect(DB::select($query, ['limit' => $limit]));
    }

    public function censusCountsBetween(string $start, string $end): Collection
    {
        $query = $this->deduplicatedCensusQuery('WHERE DATE(created_at) BETWEEN :start AND :end') . '
            ORDER BY date DESC
        ';

        return collect(DB::select($query, ['start' => $start, 'end' => $end]));
    }

    public function censusMilestones(): object
    {
        $baseQuery = $this->deduplicatedCensusQuery();

        $first = DB::selectOne("
            SELECT count AS total, date FROM ({$baseQuery}) AS agg
            ORDER BY date ASC
            LIMIT 1
        ");

        $peak = DB::selectOne("
            SELECT count AS total, date FROM ({$baseQuery}) AS agg
            ORDER BY count DESC
            LIMIT 1
        ");

        return (object) [
            'first' => $first,
            'peak'  => $peak,
        ];
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
                'count'        => $row->count,
            ];
        });
    }
}
