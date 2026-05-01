<?php

namespace App\Repositories;

use App\Enums\ActivityType;
use App\Models\Award;
use App\Models\Census;
use App\Models\Division;
use App\Models\Member;
use App\Traits\HasActivityGraph;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;

class DivisionRepository
{
    use HasActivityGraph;

    public function censusCounts(Division $division, int $limit = 52): Collection
    {
        return Census::where('division_id', $division->id)
            ->selectRaw('SUM(count) as count, SUM(weekly_active_count) as weekly_active, DATE(created_at) as date')
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('date')
            ->limit($limit)
            ->get();
    }

    public function getDivisionVoiceActivity(Division $division): array
    {
        return $this->getActivity('last_voice_activity', $division);
    }

    public function getDivisionAnniversaries(Division $division): Collection
    {
        $anniversaries = Member::select('name', 'join_date', 'clan_id', 'rank')
            ->selectRaw('TIMESTAMPDIFF(YEAR, join_date, CURRENT_DATE()) + IF(DAY(join_date) > DAY(CURRENT_DATE()), 1, 0) AS years_since_joined')
            ->whereMonth('join_date', now()->month)
            ->whereRaw('TIMESTAMPDIFF(YEAR, join_date, CURRENT_DATE()) + IF(DAY(join_date) > DAY(CURRENT_DATE()), 1, 0) IN (5, 10, 15, 20)')
            ->where('division_id', $division->id)
            ->orderByDesc('years_since_joined')
            ->orderBy('name')
            ->get();

        if ($anniversaries->isEmpty()) {
            return $anniversaries;
        }

        $milestoneYears = $anniversaries->pluck('years_since_joined')->unique();

        $tenureAwardIds = Award::whereIn('name', $milestoneYears->map(fn ($y) => "{$y} Years of Service"))
            ->pluck('id', 'name');

        $earnedAwards = DB::table('award_member')
            ->whereIn('member_id', $anniversaries->pluck('clan_id'))
            ->whereIn('award_id', $tenureAwardIds->values())
            ->where('approved', 1)
            ->get(['member_id', 'award_id'])
            ->groupBy('member_id');

        return $anniversaries->map(function ($anniversary) use ($tenureAwardIds, $earnedAwards) {
            $awardId = $tenureAwardIds->get("{$anniversary->years_since_joined} Years of Service");
            $memberAwards = $earnedAwards->get($anniversary->clan_id);

            $anniversary->has_tenure_award = $awardId
                && $memberAwards
                && $memberAwards->contains('award_id', $awardId);

            return $anniversary;
        });
    }

    public function recruitsLast6Months(int $divisionId, string $startDate, ?string $endDate = null): Collection
    {
        return DB::table('activities')
            ->where('name', ActivityType::RECRUITED->value)
            ->where('division_id', $divisionId)
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate ?? now()->endOfMonth()->toDateString())->endOfDay()])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bucket')
            ->selectRaw('DATE_FORMAT(created_at, "%b %y") as date')
            ->selectRaw('COUNT(*) as recruits')
            ->groupBy('bucket', 'date')
            ->orderBy('bucket')
            ->get();
    }

    public function removalsLast6Months(int $divisionId, string $startDate, ?string $endDate = null): Collection
    {
        return DB::table('activities')
            ->where('name', ActivityType::REMOVED->value)
            ->where('division_id', $divisionId)
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate ?? now()->endOfMonth()->toDateString())->endOfDay()])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bucket')
            ->selectRaw('DATE_FORMAT(created_at, "%b %y") as date')
            ->selectRaw('COUNT(*) as removals')
            ->groupBy('bucket', 'date')
            ->orderBy('bucket')
            ->get();
    }

    public function populationLast6Months(int $divisionId, string $startDate, ?string $endDate = null): Collection
    {
        $sub = DB::table('censuses')
            ->where('division_id', $divisionId)
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate ?? now()->endOfMonth()->toDateString())->endOfDay()])
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bucket, `count`');

        return DB::query()
            ->fromSub($sub, 'c')
            ->selectRaw('bucket')
            ->selectRaw('MAX(`count`) as count')
            ->selectRaw('DATE_FORMAT(STR_TO_DATE(CONCAT(bucket, "-01"), "%Y-%m-%d"), "%b %y") as date')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();
    }
}
