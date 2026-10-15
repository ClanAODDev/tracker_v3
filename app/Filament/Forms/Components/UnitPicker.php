<?php

namespace App\Filament\Forms\Components;

use App\Models\Division;
use App\Models\Member;
use App\Models\Unit;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class UnitPicker
{
    public const MAX_LEVELS = 4;

    public static function make(string $prefix, Closure $divisionId, bool $hydrateFromMember = false): array
    {
        return array_map(
            fn (int $depth) => self::level($prefix, $depth, $divisionId, $hydrateFromMember),
            range(1, self::MAX_LEVELS),
        );
    }

    public static function resolve(array $data, string $prefix): ?int
    {
        foreach (range(self::MAX_LEVELS, 1) as $depth) {
            if (! empty($data[$prefix . $depth])) {
                return (int) $data[$prefix . $depth];
            }
        }

        return null;
    }

    public static function present(array $data, string $prefix): bool
    {
        foreach (range(1, self::MAX_LEVELS) as $depth) {
            if (array_key_exists($prefix . $depth, $data)) {
                return true;
            }
        }

        return false;
    }

    public static function without(array $data, string $prefix): array
    {
        foreach (range(1, self::MAX_LEVELS) as $depth) {
            unset($data[$prefix . $depth]);
        }

        return $data;
    }

    private static function level(string $prefix, int $depth, Closure $divisionId, bool $hydrateFromMember): Select
    {
        $select = Select::make($prefix . $depth)
            ->nullable()
            ->searchable()
            ->reactive()
            ->label(fn (Select $component) => self::division($component->evaluate($divisionId))?->unitLevel($depth)?->label ?? 'Unit')
            ->visible(fn (Select $component) => $depth <= (self::division($component->evaluate($divisionId))?->deepestUnitLevel() ?? 0))
            ->options(function (Select $component, Get $get) use ($prefix, $depth, $divisionId) {
                $units = Unit::query()->orderBy('order')->orderBy('name');

                if ($depth === 1) {
                    $units->whereNull('parent_id')->where('division_id', $component->evaluate($divisionId));
                } else {
                    $parent = $get($prefix . ($depth - 1));

                    if (! $parent) {
                        return [];
                    }

                    $units->where('parent_id', $parent);
                }

                return $units->get()->mapWithKeys(fn (Unit $unit) => [$unit->id => $unit->name ?: 'Untitled'])->all();
            })
            ->afterStateUpdated(function (Set $set) use ($prefix, $depth) {
                foreach (range($depth + 1, self::MAX_LEVELS) as $deeper) {
                    $set($prefix . $deeper, null);
                }
            });

        if ($hydrateFromMember) {
            $select->afterStateHydrated(fn (Select $component, ?Member $record) => $component->state($record?->unitAt($depth)?->id));
        }

        return $select;
    }

    private static function division(mixed $id): ?Division
    {
        return $id ? Division::with('unitLevels')->find($id) : null;
    }
}
