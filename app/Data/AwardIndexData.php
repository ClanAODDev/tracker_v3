<?php

namespace App\Data;

use App\Models\Award;
use App\Services\AwardCatalogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AwardIndexData
{
    private Collection $awards;

    private Collection $clanAwards;

    private Collection $activeAwards;

    private Collection $legacyAwards;

    private Collection $divisionsWithAwards;

    private Collection $tieredGroups;

    private array $totals;

    public function __construct(
        private ?string $divisionSlug,
        AwardCatalogService $catalog,
    ) {
        $query = Award::active()
            ->orderBy('display_order')
            ->withCount('recipients')
            ->with('division');

        if ($divisionSlug) {
            $query->whereHas('division', fn (Builder $q) => $q->where('slug', $divisionSlug));
        }

        $allAwards = $query->get();

        $awards = $allAwards->filter(function ($award) {
            if ($award->division_id === null) {
                return true;
            }
            if ($award->division?->active) {
                return true;
            }

            return $award->recipients_count > 0;
        });

        $maxRecipients = $awards->max('recipients_count') ?: 1;
        $awards->each(function ($award) use ($maxRecipients) {
            $award->popularityPct = round($award->recipients_count / $maxRecipients * 100);
            $award->rarity        = $award->getRarity();
        });

        $this->clanAwards = $awards->whereNull('division_id')->values();

        $this->activeAwards = $awards
            ->whereNotNull('division_id')
            ->filter(fn ($award) => $award->division?->active || $award->division?->slug === $divisionSlug)
            ->groupBy('division.name')
            ->sortKeys();

        $this->legacyAwards = $awards
            ->whereNotNull('division_id')
            ->filter(fn ($award) => ! $award->division?->active && $award->recipients_count > 0 && $award->division?->slug !== $divisionSlug)
            ->groupBy('division.name')
            ->sortKeys();

        $activeAndClanAwards = $awards->filter(fn ($a) => $a->division_id === null || $a->division?->active);
        $this->totals        = [
            'awards'      => $awards->count(),
            'recipients'  => $awards->sum('recipients_count'),
            'requestable' => $activeAndClanAwards->where('allow_request', true)->count(),
        ];

        $this->divisionsWithAwards = Award::active()
            ->whereNotNull('division_id')
            ->withCount('recipients')
            ->with('division:id,name,slug,active')
            ->get()
            ->filter(fn ($award) => $award->division?->active || $award->recipients_count > 0)
            ->pluck('division')
            ->unique('id')
            ->sortBy([['active', 'desc'], ['name', 'asc']])
            ->values();

        $this->tieredGroups = collect($catalog->buildTieredGroups());
        $tieredAwardIds     = $this->tieredGroups->flatMap(fn ($group) => $group['tiers']->pluck('id'))->toArray();

        $this->clanAwards = $this->clanAwards->reject(fn ($a) => in_array($a->id, $tieredAwardIds));

        $this->activeAwards = $this->activeAwards
            ->map(fn ($group) => $group->reject(fn ($a) => in_array($a->id, $tieredAwardIds)))
            ->filter(fn ($group) => $group->isNotEmpty());

        $this->legacyAwards = $this->legacyAwards
            ->map(fn ($group) => $group->reject(fn ($a) => in_array($a->id, $tieredAwardIds)))
            ->filter(fn ($group) => $group->isNotEmpty());

        $this->awards = $awards;
    }

    public static function for(?string $divisionSlug, AwardCatalogService $catalog): self
    {
        return new self($divisionSlug, $catalog);
    }

    public function isEmpty(): bool
    {
        return $this->awards->isEmpty();
    }

    public function toArray(): array
    {
        $tieredGroups = $this->tieredGroups;

        return [
            'divisionSlug'    => $this->divisionSlug,
            'totals'          => $this->totals,
            'rarityBreakdown' => $this->awards->groupBy('rarity')->map(fn ($group) => $group->count()),
            'rarities'        => collect(config('aod.awards.rarity'))->map(fn ($r, $key) => [
                'key'   => $key,
                'label' => $r['label'],
                'min'   => $r['min'],
                'max'   => $r['max'],
            ])->values(),
            'divisions' => $this->divisionsWithAwards->map(fn ($division) => [
                'name'   => $division->name,
                'slug'   => $division->slug,
                'active' => (bool) $division->active,
            ]),
            'clan' => [
                'awards' => $this->clanAwards->values()->map(fn ($a) => $this->serializeAward($a)),
                'tiered' => $tieredGroups->whereNull('division_id')->values()->map(fn ($g) => $this->serializeTieredGroup($g)),
            ],
            'divisionSections' => $this->activeAwards->map(function ($group, $name) use ($tieredGroups) {
                $division = $group->first()->division;

                return [
                    'name'   => $name,
                    'logo'   => $division->getLogoPath(),
                    'awards' => $group->values()->map(fn ($a) => $this->serializeAward($a)),
                    'tiered' => $tieredGroups->where('division_id', $division->id)->values()
                        ->map(fn ($g) => $this->serializeTieredGroup($g)),
                ];
            })->values(),
            'legacySections' => $this->legacyAwards->map(function ($group, $name) {
                $division = $group->first()->division;

                return [
                    'name'   => $name,
                    'logo'   => $division->getLogoPath(),
                    'awards' => $group->values()->map(fn ($a) => $this->serializeAward($a, legacy: true)),
                ];
            })->values(),
        ];
    }

    private function serializeAward(Award $award, bool $legacy = false): array
    {
        return [
            'id'   => $award->id,
            'name' => $award->division
                ? Str::replace($award->division->name . ' - ', '', $award->name)
                : $award->name,
            'rarity'          => $award->rarity,
            'recipientsCount' => (int) $award->recipients_count,
            'image'           => $award->image ? $award->getImagePath() : null,
            'allowRequest'    => ! $legacy && (bool) $award->allow_request,
        ];
    }

    private function serializeTieredGroup(array $group): array
    {
        return [
            'name'           => $group['name'],
            'slug'           => $group['slug'],
            'tierCount'      => $group['tiers']->count(),
            'recipientCount' => $group['recipientCount'],
            'image'          => $group['topTier']->image ? $group['topTier']->getImagePath() : null,
        ];
    }
}
