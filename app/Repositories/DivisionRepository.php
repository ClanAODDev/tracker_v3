<?php

namespace App\Repositories;

use App\Models\Division;
use App\Traits\HasActivityGraph;

/**
 * Class DivisionRepository.
 */
class DivisionRepository
{

    use HasActivityGraph;

    /**
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function censusCounts(Division $division, $limit = 52)
    {
        return collect(\DB::select('
            SELECT sum(count) as count, sum(weekly_active_count) as weekly_active, DATE(created_at) as date
            FROM censuses 
            WHERE division_id = :division_id
            GROUP BY DATE(created_at) 
            ORDER BY date DESC 
            LIMIT :limit
        ', [
            'division_id' => $division->id,
            'limit' => $limit,
        ]));

    }

    /**
     * @return array
     */
    public function getPromotionsData(Division $division)
    {
        $members = $division->members()->whereBetween('last_promoted',
            [\Carbon\Carbon::now()->startOfMonth(), \Carbon\Carbon::now()->endOfMonth()])->get();

        return [
            'labels' => ['Less than 2 weeks', 'Less than 1 month', 'More than 1 month'],
            'values' => [$members->groupBy('rank.name')], 'colors' => ['#28b62c', '#ff851b', '#ff4136'],
        ];
    }

    public function getDivisionVoiceActivity(Division $division)
    {
        return $this->getActivity('last_voice_activity', $division);
    }

    public function getDivisionAnniversaries(Division $division)
    {
        return \App\Models\Member::select('name', 'join_date', 'clan_id')
            ->selectRaw('TIMESTAMPDIFF(YEAR, join_date, CURRENT_DATE()) AS years_since_joined, 
                     IF(DAY(join_date) > DAY(CURRENT_DATE()), 1, 0) AS adjustment')
            ->whereRaw('TIMESTAMPDIFF(YEAR, join_date, CURRENT_DATE()) >= 1')
            ->whereMonth('join_date', now()->month)
            ->where('division_id', $division->id)
            ->orderByDesc('years_since_joined')
            ->orderBy('name')
            ->get()
            ->map(function ($member) {
                // Assuming the anniversary if the day hasn't yet occurred this month
                $member->years_since_joined += $member->adjustment;
                unset($member->adjustment);

                return $member;
            });
    }

    /**
     * @return array
     */
    public function getRankDemographic(Division $division)
    {
        $ranks = \DB::select('ranks.abbreviation')->addSelect(\DB::raw('count(*) as count'))->from('members')->join('ranks',
            function ($join) {
                $join->on('ranks.id', '=', 'members.rank_id');
            })->join('division_member', function ($join) {
                $join->on('member_id', '=', 'members.id');
            })->where('division_id', '=', $division->id)->groupBy('rank_id')->get();
        $labels = [];
        $values = [];
        foreach ($ranks as $rank) {
            $labels[] = $rank->abbreviation;
            $values[] = $rank->count;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @param  null  $startDate
     * @return mixed
     */
    public function recruitsLast6Months($divisionId, $startDate)
    {
        return \DB::table('activities')->selectRaw('DATE_FORMAT(created_at, "%b %y") as date')->selectRaw('count(*) as recruits')->from('activities')->where('activities.name',
            '=', 'recruited_member')->where('division_id', '=', $divisionId)->where('created_at', '>=',
                $startDate)->orderBy('activities.created_at')->groupby('date')->get();
    }

    /**
     * @param  null  $startDate
     * @return mixed
     */
    public function removalsLast6Months($divisionId, $startDate)
    {
        return \DB::table('activities')->selectRaw('DATE_FORMAT(created_at, "%b %y") as date')
            ->selectRaw('count(*) as removals')->from('activities')
            ->where('activities.name', '=', 'removed_member')
            ->where('created_at', '>=', $startDate)
            ->where('division_id', '=', $divisionId)->groupby('date')
            ->orderBy('activities.created_at', 'ASC')->get();
    }

    /**
     * @param  null  $startDate
     * @return mixed
     */
    public function populationLast6Months($divisionId, $startDate)
    {
        return \DB::table('censuses')->selectRaw('DATE_FORMAT(created_at, "%b %y") as date')->selectRaw('count')->from('censuses')->where('division_id',
            '=', $divisionId)->where('created_at', '>=', $startDate)->groupby('date')->orderBy('created_at',
                'ASC')->get();
    }
}
