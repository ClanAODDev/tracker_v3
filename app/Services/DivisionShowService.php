<?php

namespace App\Services;

use App\Data\CensusChartData;
use App\Data\DivisionShowData;
use App\Data\DivisionStatsData;
use App\Data\PendingActionsData;
use App\Enums\ActivityType;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Repositories\DivisionRepository;

class DivisionShowService
{
    public function __construct(
        private DivisionRepository $divisionRepository,
    ) {}

    public function getShowData(Division $division): DivisionShowData
    {
        $latestCensus = $division->latestCensus;
        $stats        = DivisionStatsData::fromDivision($division, $latestCensus);

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
            recentActivity: $this->getRecentActivity($division),
            pendingApplicationCount: $division->settings()->get('application_required', false)
                ? DivisionApplication::pending()->where('division_id', $division->id)->count()
                : 0,
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

    private function getRecentActivity(Division $division)
    {
        $activities = $division->activity()
            ->whereIn('name', ActivityType::feedTypes())
            ->with(['subject' => fn ($q) => $q->withTrashed(), 'user'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return $this->groupConsecutiveActivities($activities);
    }

    private function groupConsecutiveActivities($activities)
    {
        if ($activities->isEmpty()) {
            return collect();
        }

        $grouped      = collect();
        $currentGroup = null;

        foreach ($activities as $activity) {
            if ($currentGroup === null || $currentGroup['type'] !== $activity->name) {
                if ($currentGroup !== null) {
                    $grouped->push($currentGroup);
                }
                $currentGroup = [
                    'type'       => $activity->name,
                    'events'     => collect([$activity]),
                    'created_at' => $activity->created_at,
                ];
            } else {
                $currentGroup['events']->push($activity);
            }
        }

        if ($currentGroup !== null) {
            $grouped->push($currentGroup);
        }

        return $grouped->take(10);
    }
}
