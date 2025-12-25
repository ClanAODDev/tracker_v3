<?php

namespace App\Services;

use App\Data\CensusChartData;
use App\Data\DivisionShowData;
use App\Data\DivisionStatsData;
use App\Data\PendingActionsData;
use App\Models\Division;
use App\Repositories\DivisionRepository;

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
            pendingActions: PendingActionsData::forDivision($division, auth()->user()),
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
}
