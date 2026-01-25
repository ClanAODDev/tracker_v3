<?php

namespace App\Filament\Forms\Components;

use App\Models\Division;
use Filament\Forms\Components\CheckboxList;

class PartTimeDivisionsForm
{
    public const FIELD = 'partTimeDivisions';

    public static function makeUsingFormModel(): CheckboxList
    {
        return CheckboxList::make(self::FIELD)
            ->hiddenLabel()
            ->options(function ($get, $set, $state, $livewire) {
                $member = ($livewire->record ?? null) ?: (auth()->user()->member ?? null);
                if (! $member) {
                    return [];
                }

                return self::getValidOptions($member);
            })
            ->default(function ($livewire) {
                $member = ($livewire->record ?? null) ?: (auth()->user()->member ?? null);
                if (! $member) {
                    return [];
                }

                $validOptionIds = array_keys(self::getValidOptions($member));
                $memberPartTimeIds = $member->partTimeDivisions()->pluck('divisions.id')->all();

                return array_values(array_intersect($memberPartTimeIds, $validOptionIds));
            })
            ->afterStateHydrated(function (callable $set, $state, $livewire) {
                $member = ($livewire->record ?? null) ?: (auth()->user()->member ?? null);
                if (! $member) {
                    $set(self::FIELD, []);

                    return;
                }

                $validOptionIds = array_keys(self::getValidOptions($member));
                $memberPartTimeIds = $member->partTimeDivisions()->pluck('divisions.id')->all();

                $set(self::FIELD, array_values(array_intersect($memberPartTimeIds, $validOptionIds)));
            })
            ->columns(3)
            ->bulkToggleable()
            ->dehydrated(false);
    }

    protected static function getValidOptions($member): array
    {
        $excluded = Division::whereIn('name', ['Floater', "Bluntz' Reserves"])
            ->pluck('id')->toArray();

        return Division::active()
            ->whereNotIn('id', $excluded)
            ->when($member->division_id, fn ($q) => $q->where('id', '!=', $member->division_id))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function selectedFrom(array $formState): array
    {
        return collect($formState[self::FIELD] ?? [])
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }

    /** Helper to sync (only active divisions) */
    public static function sync($member, array $selectedIds): void
    {
        $activeIds = Division::active()->pluck('id')->all();
        $ids = array_values(array_intersect($selectedIds, $activeIds));
        $member->partTimeDivisions()->sync($ids);
    }
}
