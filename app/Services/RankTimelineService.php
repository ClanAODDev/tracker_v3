<?php

namespace App\Services;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RankTimelineService
{
    public function buildTimeline(Member $member, Collection $rankHistory): object
    {
        $chronologicalHistory = $rankHistory->sortBy('created_at')->values();
        $progressionOnly      = $this->filterToProgression($chronologicalHistory);
        $isOfficer            = $member->rank->isOfficer();

        $nodes        = $this->buildNodes($member, $progressionOnly, $isOfficer);
        $historyItems = $this->buildHistoryItems($member, $chronologicalHistory);

        return (object) [
            'nodes'        => $nodes,
            'historyItems' => $historyItems,
            'hasHistory'   => $chronologicalHistory->count() > 0 || $member->join_date !== null,
        ];
    }

    private function filterToProgression(Collection $history): Collection
    {
        if ($history->isEmpty()) {
            return collect();
        }

        $highWaterMark = 0;
        $lastEntry     = $history->last();

        $progression = $history->filter(function ($entry) use (&$highWaterMark) {
            if ($entry->rank->value > $highWaterMark) {
                $highWaterMark = $entry->rank->value;

                return true;
            }

            return false;
        })->values();

        if ($progression->isEmpty() || $progression->last()->rank->value !== $lastEntry->rank->value) {
            $progression->push($lastEntry);
        }

        return $progression;
    }

    private function buildNodes(Member $member, Collection $progressionOnly, bool $isOfficer): Collection
    {
        $nodes     = collect();
        $nodeIndex = 0;

        $nodes->push($this->createJoinNode($member, $nodeIndex));
        $nodeIndex++;

        if ($isOfficer && $progressionOnly->count() > 0) {
            $enlistedRanks = $progressionOnly->filter(fn ($e) => ! $e->rank->isOfficer());
            $officerRanks  = $progressionOnly->filter(fn ($e) => $e->rank->isOfficer())->values();

            if ($enlistedRanks->count() > 0 && $officerRanks->count() > 0) {
                $nodes = $this->addEnlistedConsolidatedNode($nodes, $member, $enlistedRanks, $officerRanks, $nodeIndex);
                $nodeIndex++;

                foreach ($officerRanks as $index => $entry) {
                    $duration = null;
                    if ($index < $officerRanks->count() - 1) {
                        $nextEntry = $officerRanks->get($index + 1);
                        $duration  = $this->formatDuration($entry->created_at, $nextEntry->created_at);
                    }

                    $nodes->push($this->createRankNode($entry, $nodeIndex, $duration));
                    $nodeIndex++;
                }
            } else {
                $nodes = $this->addStandardNodes($nodes, $member, $progressionOnly, $nodeIndex);
            }
        } else {
            $nodes = $this->addStandardNodes($nodes, $member, $progressionOnly, $nodeIndex);
        }

        return $nodes;
    }

    private function addStandardNodes(Collection $nodes, Member $member, Collection $progression, int &$nodeIndex): Collection
    {
        if ($member->join_date && $progression->count() > 0) {
            $firstPromotion          = $progression->first();
            $initialDuration         = $this->formatDuration($member->join_date, $firstPromotion->created_at);
            $nodes->last()->duration = $initialDuration;
        }

        foreach ($progression as $index => $entry) {
            $duration = null;
            if ($index < $progression->count() - 1) {
                $nextEntry = $progression->get($index + 1);
                $duration  = $this->formatDuration($entry->created_at, $nextEntry->created_at);
            }

            $nodes->push($this->createRankNode($entry, $nodeIndex, $duration));
            $nodeIndex++;
        }

        return $nodes;
    }

    private function addEnlistedConsolidatedNode(
        Collection $nodes,
        Member $member,
        Collection $enlistedRanks,
        Collection $officerRanks,
        int $nodeIndex
    ): Collection {
        $enlistedStart     = $member->join_date ?? $enlistedRanks->first()->created_at;
        $firstEnlistedDate = $enlistedRanks->first()->created_at;
        $lastEnlistedDate  = $enlistedRanks->last()->created_at;
        $firstOfficerDate  = $officerRanks->first()->created_at;

        $nodes->last()->duration = $this->formatDuration($enlistedStart, $firstEnlistedDate);

        $durationToFirstOfficer = $this->formatDuration($lastEnlistedDate, $firstOfficerDate);

        $nodes->push((object) [
            'type'      => 'consolidated',
            'label'     => 'Enlisted',
            'dateRange' => $firstEnlistedDate->format('M Y') . ' - ' . $lastEnlistedDate->format('M Y'),
            'position'  => $nodeIndex % 2 === 0 ? 'left' : 'right',
            'duration'  => $durationToFirstOfficer,
        ]);

        return $nodes;
    }

    private function createJoinNode(Member $member, int $nodeIndex): object
    {
        return (object) [
            'type'     => 'join',
            'date'     => $member->join_date?->format('M Y'),
            'label'    => 'Joined AOD',
            'position' => $nodeIndex % 2 === 0 ? 'left' : 'right',
            'duration' => null,
        ];
    }

    private function createRankNode(object $entry, int $nodeIndex, ?string $duration): object
    {
        return (object) [
            'type'     => 'promotion',
            'rank'     => $entry->rank->getAbbreviation(),
            'date'     => $entry->created_at->format('M Y'),
            'position' => $nodeIndex % 2 === 0 ? 'left' : 'right',
            'duration' => $duration,
        ];
    }

    private function buildHistoryItems(Member $member, Collection $chronologicalHistory): Collection
    {
        $items        = collect();
        $joinDate     = $member->join_date;
        $joinInserted = false;

        $prevRank = null;
        foreach ($chronologicalHistory as $entry) {
            if ($joinDate && ! $joinInserted && $joinDate->lte($entry->created_at)) {
                $items->push((object) [
                    'type'  => 'join',
                    'date'  => $joinDate->format('M j, Y'),
                    'label' => 'Joined AOD',
                ]);
                $joinInserted = true;
            }

            $isDemotion = $prevRank && $entry->rank->value < $prevRank->value;

            $items->push((object) [
                'type' => $isDemotion ? 'demotion' : 'promotion',
                'date' => $entry->created_at->format('M j, Y'),
                'rank' => $entry->rank->getAbbreviation(),
            ]);

            $prevRank = $entry->rank;
        }

        if ($joinDate && ! $joinInserted) {
            $items->push((object) [
                'type'  => 'join',
                'date'  => $joinDate->format('M j, Y'),
                'label' => 'Joined AOD',
            ]);
        }

        return $items;
    }

    private function formatDuration(Carbon $start, Carbon $end): string
    {
        $months          = (int) $start->diffInMonths($end);
        $years           = (int) floor($months / 12);
        $remainingMonths = $months % 12;

        if ($years > 0) {
            return $remainingMonths > 0
                ? "{$years}y {$remainingMonths}m"
                : "{$years}y";
        }

        return "{$months}m";
    }
}
