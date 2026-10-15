<?php

namespace App\Support;

use App\Models\Division;
use App\Models\Unit;
use Illuminate\Support\Collection;

class UnitTree
{
    public static function for(Division $division): array
    {
        $byParent = $division->units()
            ->withCount('members')
            ->with('leader:clan_id,name')
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->groupBy('parent_id');

        return self::branch($division, $byParent, null);
    }

    private static function branch(Division $division, Collection $byParent, ?int $parentId): array
    {
        return ($byParent->get($parentId) ?? collect())->map(function (Unit $unit) use ($division, $byParent) {
            $children = self::branch($division, $byParent, $unit->id);

            return [
                'id'           => $unit->id,
                'name'         => $unit->name ?: 'Untitled',
                'levelLabel'   => $division->unitLevel($unit->depth)?->label ?? 'Unit',
                'membersCount' => $unit->members_count + array_sum(array_column($children, 'membersCount')),
                'leaderName'   => $unit->leader?->name,
                'children'     => $children,
            ];
        })->values()->all();
    }
}
