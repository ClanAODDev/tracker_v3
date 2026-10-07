<?php

namespace App\Services\Units;

use App\Models\Division;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyUnitSync
{
    public function sync(): array
    {
        return DB::transaction(function () {
            $platoons = $this->syncPlatoons();
            $squads   = $this->syncSquads();
            $removed  = $this->removeUnitsWithoutLegacyRow();
            $this->rebuildPaths();
            $members = $this->assignMembers();
            $levels  = $this->createMissingLevels();

            return compact('platoons', 'squads', 'removed', 'members', 'levels');
        });
    }

    private function syncPlatoons(): int
    {
        $divisions = DB::table('divisions')->pluck('id')->flip();

        $rows = DB::table('platoons')->get()->map(fn ($platoon) => [
            'legacy_type' => Unit::LEGACY_PLATOON,
            'legacy_id'   => $platoon->id,
            'division_id' => $divisions->has($platoon->division_id) ? $platoon->division_id : null,
            'parent_id'   => null,
            'depth'       => 1,
            'name'        => $platoon->name,
            'description' => $platoon->description,
            'logo'        => $platoon->logo,
            'order'       => $platoon->order,
            'gen_pop'     => false,
            'leader_id'   => $platoon->leader_id ?: null,
            'created_at'  => $platoon->created_at,
            'updated_at'  => $platoon->updated_at,
            'deleted_at'  => $platoon->deleted_at,
        ]);

        $this->upsert($rows->all());

        return $rows->count();
    }

    private function syncSquads(): int
    {
        $platoonUnits = DB::table('units')
            ->where('legacy_type', Unit::LEGACY_PLATOON)
            ->get(['id', 'legacy_id', 'division_id', 'deleted_at'])
            ->keyBy('legacy_id');

        $rows = DB::table('squads')->get()->map(function ($squad) use ($platoonUnits) {
            $parent = $platoonUnits->get($squad->platoon_id);

            return [
                'legacy_type' => Unit::LEGACY_SQUAD,
                'legacy_id'   => $squad->id,
                'division_id' => $parent?->division_id,
                'parent_id'   => $parent?->id,
                'depth'       => 2,
                'name'        => $squad->name,
                'description' => null,
                'logo'        => $squad->logo,
                'order'       => 0,
                'gen_pop'     => (bool) $squad->gen_pop,
                'leader_id'   => $squad->leader_id ?: null,
                'created_at'  => $squad->created_at,
                'updated_at'  => $squad->updated_at,
                'deleted_at'  => $squad->deleted_at ?? $parent?->deleted_at ?? ($parent ? null : ($squad->updated_at ?? now())),
            ];
        });

        $this->upsert($rows->all());

        return $rows->count();
    }

    private function upsert(array $rows): void
    {
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('units')->upsert(
                $chunk,
                ['legacy_type', 'legacy_id'],
                ['division_id', 'parent_id', 'depth', 'name', 'description', 'logo', 'order', 'gen_pop', 'leader_id', 'created_at', 'updated_at', 'deleted_at'],
            );
        }
    }

    private function removeUnitsWithoutLegacyRow(): int
    {
        $removed = 0;

        foreach ([Unit::LEGACY_SQUAD => 'squads', Unit::LEGACY_PLATOON => 'platoons'] as $type => $table) {
            $removed += DB::table('units')
                ->where('legacy_type', $type)
                ->whereNotExists(fn ($query) => $query->from($table)->whereColumn("{$table}.id", 'units.legacy_id'))
                ->delete();
        }

        return $removed;
    }

    private function rebuildPaths(): void
    {
        DB::table('units')->where('depth', 1)->update(['path' => DB::raw("concat('/', id, '/')")]);

        DB::table('units as child')
            ->join('units as parent', 'parent.id', '=', 'child.parent_id')
            ->where('child.depth', 2)
            ->update(['child.path' => DB::raw("concat(parent.path, child.id, '/')")]);

        DB::table('units')->where('depth', 2)->whereNull('parent_id')->update(['path' => DB::raw("concat('/', id, '/')")]);
    }

    private function assignMembers(): int
    {
        DB::table('members')->whereNotNull('unit_id')->update(['unit_id' => null]);

        $squads = DB::table('members as m')
            ->join('units as squad', fn ($join) => $join->on('squad.legacy_id', '=', 'm.squad_id')->where('squad.legacy_type', Unit::LEGACY_SQUAD))
            ->join('units as platoon', 'platoon.id', '=', 'squad.parent_id')
            ->where('m.squad_id', '>', 0)
            ->whereColumn('platoon.legacy_id', 'm.platoon_id')
            ->whereColumn('squad.division_id', 'm.division_id')
            ->whereNull('squad.deleted_at')
            ->update(['m.unit_id' => DB::raw('squad.id')]);

        $platoons = DB::table('members as m')
            ->join('units as platoon', fn ($join) => $join->on('platoon.legacy_id', '=', 'm.platoon_id')->where('platoon.legacy_type', Unit::LEGACY_PLATOON))
            ->where('m.platoon_id', '>', 0)
            ->where('m.squad_id', 0)
            ->whereColumn('platoon.division_id', 'm.division_id')
            ->whereNull('platoon.deleted_at')
            ->update(['m.unit_id' => DB::raw('platoon.id')]);

        return $squads + $platoons;
    }

    private function createMissingLevels(): int
    {
        $created = 0;
        $now     = now();

        Division::withTrashed()->get()->each(function (Division $division) use (&$created, $now) {
            foreach ([1 => ['platoon', 'platoon leader'], 2 => ['squad', 'squad leader']] as $depth => [$unit, $leader]) {
                $label = $division->locality($unit);

                $created += DB::table('division_unit_levels')->insertOrIgnore([
                    'division_id'  => $division->id,
                    'depth'        => $depth,
                    'label'        => $label,
                    'label_plural' => Str::plural($label),
                    'leader_title' => $division->locality($leader),
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }
        });

        return $created;
    }
}
