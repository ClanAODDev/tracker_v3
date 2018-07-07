<?php

namespace App\Repositories;

use App\Division;
use Carbon\Carbon;
use DB;

/**
 * Class DivisionRepository
 *
 * @package App\Repositories
 */
class DivisionRepository
{

    /**
     * @param Division $division
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function censusCounts(Division $division, $limit = 52)
    {
        $censuses = collect(DB::select(
            DB::raw("    
                SELECT sum(count) as count, sum(weekly_active_count) as weekly_active, created_at as date 
                FROM censuses WHERE division_id = {$division->id} 
                GROUP BY date(created_at) 
                ORDER BY date DESC LIMIT {$limit};
            ")
        ));

        return $censuses;
    }

    /**
     * @param Division $division
     * @return array
     */
    public function getPromotionsData(Division $division)
    {
        $members = $division->members()
            ->whereBetween('last_promoted', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])->get();

        return [
            'labels' => ['Less than 2 weeks', 'Less than 1 month', 'More than 1 month'],
            'values' => [$members->groupBy('rank.name')],
            'colors' => ['#28b62c', '#ff851b', '#ff4136']
        ];
    }

    /**
     * @param Division $division
     * @return array
     */
    public function getDivisionActivity(Division $division)
    {
        $twoWeeksAgo = Carbon::now()->subDays(14);
        $oneMonthAgo = Carbon::now()->subDays(30);

        $twoWeeks = $division->members()->where('last_activity', '>=', $twoWeeksAgo);

        $oneMonth = $division->members()->where('last_activity', '<=', $twoWeeksAgo)
            ->where('last_activity', '>=', $oneMonthAgo);

        $moreThanOneMonth = $division->members()->where('last_activity', '<=', $oneMonthAgo);

        return [
            'labels' => ['Less than 2 weeks', 'Less than 1 month', 'More than 1 month'],
            'values' => [$twoWeeks->count(), $oneMonth->count(), $moreThanOneMonth->count()],
            'colors' => ['#28b62c', '#ff851b', '#ff4136']
        ];
    }

    /**
     * @param Division $division
     * @return array
     */
    public function getDivisionTSActivity(Division $division)
    {
        $twoWeeksAgo = Carbon::now()->subDays(14);
        $oneMonthAgo = Carbon::now()->subDays(30);

        $twoWeeks = $division->members()->where('last_ts_activity', '>=', $twoWeeksAgo);

        $oneMonth = $division->members()->where('last_ts_activity', '<=', $twoWeeksAgo)
            ->where('last_ts_activity', '>=', $oneMonthAgo);

        $moreThanOneMonth = $division->members()->where('last_ts_activity', '<=', $oneMonthAgo);

        return [
            'labels' => ['Less than 2 weeks', 'Less than 1 month', 'More than 1 month'],
            'values' => [$twoWeeks->count(), $oneMonth->count(), $moreThanOneMonth->count()],
            'colors' => ['#28b62c', '#ff851b', '#ff4136']
        ];
    }

    /**
     * @param Division $division
     * @return array
     */
    public function getRankDemographic(Division $division)
    {
        $ranks = DB::select('ranks.abbreviation')
            ->addSelect(DB::raw('count(*) as count'))
            ->from('members')
            ->join('ranks', function ($join) {
                $join->on('ranks.id', '=', 'members.rank_id');
            })
            ->join('division_member', function ($join) {
                $join->on('member_id', '=', 'members.id');
            })
            ->where('division_id', '=', $division->id)
            ->groupBy('rank_id')
            ->get();

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

    /**
     * @param $divisionId
     * @return mixed
     */
    public function recruitsLast6Months($divisionId)
    {
        return DB::table('activities')
            ->selectRaw('DATE_FORMAT(created_at, "%b %y") as date')
            ->selectRaw('count(*) as recruits')
            ->from('activities')
            ->where('activities.name', '=', 'recruited_member')
            ->where('division_id', '=', $divisionId)
            ->where('created_at', '>=', Carbon::now()->subDays(180))
            ->orderBy('activities.created_at')
            ->groupby('date')
            ->get();
    }

    /**
     * @param $divisionId
     * @return mixed
     */
    public function removalsLast6Months($divisionId)
    {
        return DB::table('activities')
            ->selectRaw('DATE_FORMAT(created_at, "%b %y") as date')
            ->selectRaw('count(*) as removals')
            ->from('activities')
            ->where('activities.name', '=', 'removed_member')
            ->where('created_at', '>=', Carbon::now()->subDays(180))
            ->where('division_id', '=', $divisionId)
            ->groupby('date')
            ->orderBy('activities.created_at', 'ASC')
            ->get();
    }

    /**
     * @param $divisionId
     * @return mixed
     */
    public function populationLast6Months($divisionId)
    {
        return DB::table('censuses')
            ->selectRaw('DATE_FORMAT(created_at, "%b %y") as date')
            ->selectRaw('count')
            ->from('censuses')
            ->where('division_id', '=', $divisionId)
            ->where('created_at', '>=', Carbon::now()->subDays(180))
            ->groupby('date')
            ->orderBy('created_at', 'ASC')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function withoutSsgts()
    {
        return Division::doesntHave('staffSergeants')->get();
    }
}
