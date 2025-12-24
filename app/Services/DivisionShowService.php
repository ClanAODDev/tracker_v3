<?php

namespace App\Services;

use App\Data\CensusChartData;
use App\Data\DivisionShowData;
use App\Data\DivisionStatsData;
use App\Models\Division;
use App\Repositories\DivisionRepository;
use Carbon\Carbon;

class DivisionShowService
{
    public function __construct(
        private DivisionRepository $divisionRepository,
    ) {}

    public function getShowData(Division $division): DivisionShowData
    {
        $latestCensus = $division->latestCensus;
        $stats = DivisionStatsData::fromDivision($division, $latestCensus);

        return new DivisionShowData(
            division: $division,
            stats: $stats,
            chartData: CensusChartData::fromDivision($division),
            platoons: $this->getPlatoons($division, $stats->activityThresholdDays),
            divisionLeaders: $division->leaders()->get(),
            generalSergeants: $division->generalSergeants()->get(),
            divisionAnniversaries: $this->divisionRepository->getDivisionAnniversaries($division),
            previousCensus: $this->divisionRepository->censusCounts($division)->first(),
            outstandingInactives: $this->getOutstandingInactives($division),
            outstandingAwardRequests: $division->awards()->whereHas('unapprovedRecipients')->count(),
        );
    }

    private function getPlatoons(Division $division, int $activityThresholdDays)
    {
        return $division->platoons()
            ->with(['squads.leader', 'leader'])
            ->withCount([
                'members',
                'members as voice_active_count' => function ($query) use ($activityThresholdDays) {
                    $query->where('last_voice_activity', '>=', now()->subDays($activityThresholdDays));
                },
            ])
            ->orderBy('order')
            ->get();
    }

    private function getOutstandingInactives(Division $division): int
    {
        $maxDays = config('aod.maximum_days_inactive');

        return $division->members()
            ->whereDoesntHave('leave')
            ->where('last_voice_activity', '<', Carbon::now()->subDays($maxDays)->format('Y-m-d'))
            ->count();
    }
}
