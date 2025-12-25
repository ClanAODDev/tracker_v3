<?php

namespace App\Traits;

use App\Models\Division;
use Carbon\CarbonImmutable;

trait HasActivityGraph
{
    private array $activityColors = [
        '#28b62c', '#ff851b', '#ff4136', '#000',
    ];

    public function getActivity(string $field, $unit, int $buckets = 3): array
    {
        $division = $unit instanceof Division
            ? $unit
            : $unit->division;

        $maxDays = $division->settings()->get('inactivity_days') ?? 90;
        $now = CarbonImmutable::now();
        $members = $unit->members();

        // Define cutoffs including the final maxDays
        $cutoffs = collect(range(1, $buckets))
            ->map(fn ($i) => (int) round($maxDays * $i / $buckets))
            ->all();

        $labels = [];
        $values = [];
        $colors = array_slice($this->activityColors, 0, $buckets + 1); // +1 for final bucket

        $prevCutoff = 0;
        $prevThreshold = $now;

        foreach ($cutoffs as $cutoff) {
            $currentThreshold = $now->subDays($cutoff);

            $labels[] = "{$prevCutoff}-{$cutoff} days";

            $query = $members->clone()
                ->where($field, '<', $prevThreshold)
                ->where($field, '>=', $currentThreshold);

            $values[] = $query->count();

            $prevCutoff = $cutoff;
            $prevThreshold = $currentThreshold;
        }

        $labels[] = ">{$maxDays} days";
        $values[] = $members->clone()
            ->where($field, '<', $now->subDays($maxDays))
            ->count();

        return compact('labels', 'values', 'colors');
    }
}
