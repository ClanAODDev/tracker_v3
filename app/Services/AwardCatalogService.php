<?php

namespace App\Services;

use App\Models\Award;
use App\Models\MemberAward;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AwardCatalogService
{
    /**
     * Group every award that has a prerequisite chain (in either direction) into
     * ordered tiers, so `AwardIndexData`/`AwardController::tiered()` can present
     * each chain as one entry instead of one per tier.
     */
    public function buildTieredGroups(): array
    {
        $awardsWithChains = Award::active()
            ->where(function ($q) {
                $q->whereNotNull('prerequisite_award_id')
                    ->orWhereHas('dependents');
            })
            ->withCount('recipients')
            ->with('division')
            ->orderBy('display_order')
            ->get();

        $awardsById   = $awardsWithChains->keyBy('id');
        $dependentMap = $awardsWithChains
            ->filter(fn ($a) => $a->prerequisite_award_id !== null)
            ->keyBy('prerequisite_award_id');

        // One query for every tiered award's recipients instead of one `count()`
        // query per chain below — grouped by award so each chain's distinct
        // member count can still be computed in memory.
        $recipientsByAward = MemberAward::whereIn('award_id', $awardsWithChains->pluck('id'))
            ->where('approved', true)
            ->whereHas('member', fn ($q) => $q->where('division_id', '>', 0))
            ->get(['award_id', 'member_id'])
            ->groupBy('award_id');

        $processed = [];
        $groups    = [];

        foreach ($awardsWithChains as $award) {
            if (in_array($award->id, $processed)) {
                continue;
            }

            $chain   = collect([$award]);
            $current = $awardsById->get($award->prerequisite_award_id);
            while ($current) {
                $chain->push($current);
                $current = $awardsById->get($current->prerequisite_award_id);
            }

            $current = $award;
            while ($next = $dependentMap->get($current->id)) {
                $chain->push($next);
                $current = $next;
            }

            $chain = $chain->unique('id')->sortBy('display_order')->values();

            $chainIds  = $chain->pluck('id')->toArray();
            $processed = array_merge($processed, $chainIds);

            $topTier        = $chain->last();
            $recipientCount = collect($chainIds)
                ->flatMap(fn ($id) => $recipientsByAward->get($id, collect())->pluck('member_id'))
                ->unique()
                ->count();

            $baseTier  = $chain->first();
            $groupName = $baseTier->tiered_group_name ?? $this->getTieredGroupName($chain);
            $division  = $baseTier->division;
            $groups[]  = [
                'name'           => $groupName,
                'slug'           => Str::slug($groupName),
                'description'    => $baseTier->tiered_group_description ?? $this->getTieredGroupDescription($chain, $groupName),
                'tiers'          => $chain,
                'topTier'        => $topTier,
                'recipientCount' => $recipientCount,
                'division_id'    => $division?->id,
                'division'       => $division,
            ];
        }

        return $groups;
    }

    private function getTieredGroupName(Collection $chain): string
    {
        $names = $chain->pluck('name')->toArray();

        if (collect($names)->contains(fn ($n) => str_contains($n, 'Years of Service'))) {
            return 'AOD Tenure';
        }

        $commonWords = [];
        $firstWords  = explode(' ', $names[0]);
        foreach ($firstWords as $word) {
            if (collect($names)->every(fn ($n) => str_contains($n, $word))) {
                $commonWords[] = $word;
            }
        }

        return ! empty($commonWords) ? implode(' ', $commonWords) : $chain->first()->name . ' Series';
    }

    private function getTieredGroupDescription(Collection $chain, string $groupName): string
    {
        $tierCount = $chain->count();
        $topTier   = $chain->last();

        if (str_contains($groupName, 'Tenure')) {
            return 'Recognition for years of dedicated service to the Angels of Death clan. Each tier represents a milestone in your AOD journey.';
        }

        return "A {$tierCount}-tier progression culminating in {$topTier->name}. Earn each tier in sequence to complete the set.";
    }
}
