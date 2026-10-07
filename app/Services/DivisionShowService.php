<?php

namespace App\Services;

use App\Data\CensusChartData;
use App\Data\DivisionShowData;
use App\Data\DivisionStatsData;
use App\Data\PendingActionsData;
use App\Enums\ActivityType;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\Unit;
use App\Repositories\DivisionRepository;

class DivisionShowService
{
    public function __construct(
        private DivisionRepository $divisionRepository,
    ) {}

    public function getShowData(Division $division): DivisionShowData
    {
        $latestCensus = $division->latestCensus;
        $stats        = DivisionStatsData::fromDivision($division);

        return new DivisionShowData(
            division: $division,
            stats: $stats,
            chartData: CensusChartData::fromDivision($division),
            platoons: $this->getPlatoons($division, $stats->activityThresholdDays),
            divisionLeaders: $division->leaders()->with('division')->get(),

            divisionAnniversaries: $this->divisionRepository->getDivisionAnniversaries($division),
            previousCensus: $this->divisionRepository->censusCounts($division)->first(),
            pendingActions: PendingActionsData::forDivision($division, auth()->user()),
            recentActivity: $this->getRecentActivity($division),
            pendingApplicationCount: $division->settings()->get('application_required', false)
                ? DivisionApplication::where('division_id', $division->id)->count()
                : 0,
        );
    }

    private function getPlatoons(Division $division, int $activityThresholdDays)
    {
        $counts = $division->members()
            ->whereNotNull('unit_id')
            ->selectRaw('unit_id, count(*) as total, sum(last_voice_activity >= ?) as voice_active', [now()->subDays($activityThresholdDays)])
            ->groupBy('unit_id')
            ->get()
            ->keyBy('unit_id');

        $count = fn (Unit $unit, string $column) => (int) ($counts->get($unit->id)?->{$column} ?? 0);

        return $division->topUnits()
            ->with(['children.leader.division', 'leader.division'])
            ->get()
            ->each(function (Unit $platoon) use ($count) {
                $platoon->children->each(fn (Unit $squad) => $squad->setAttribute('members_count', $count($squad, 'total')));
                $platoon->setAttribute('members_count', $count($platoon, 'total') + $platoon->children->sum(fn (Unit $squad) => $count($squad, 'total')));
                $platoon->setAttribute('voice_active_count', $count($platoon, 'voice_active') + $platoon->children->sum(fn (Unit $squad) => $count($squad, 'voice_active')));
            });
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
