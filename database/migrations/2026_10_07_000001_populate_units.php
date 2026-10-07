<?php

use App\Models\Division;
use App\Models\DivisionUnitLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platoons') || ! Schema::hasTable('squads')) {
            return;
        }

        DB::transaction(function () {
            $this->syncPlatoons();
            $this->syncSquads();
            $this->removeUnitsWithoutLegacyRow();
            $this->rebuildPaths();
            $this->assignMembers();

            Division::withTrashed()->get()->each(fn (Division $division) => DivisionUnitLevel::createDefaultsFor($division));
        });
    }

    public function down(): void {}

    private function syncPlatoons(): void
    {
        $divisions = DB::table('divisions')->pluck('id')->flip();

        $rows = DB::table('platoons')->get()->map(fn ($platoon) => [
            'legacy_type' => 'platoon',
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
    }

    private function syncSquads(): void
    {
        $platoonUnits = DB::table('units')
            ->where('legacy_type', 'platoon')
            ->get(['id', 'legacy_id', 'division_id', 'deleted_at'])
            ->keyBy('legacy_id');

        $rows = DB::table('squads')->get()->map(function ($squad) use ($platoonUnits) {
            $parent = $platoonUnits->get($squad->platoon_id);

            return [
                'legacy_type' => 'squad',
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

    private function removeUnitsWithoutLegacyRow(): void
    {
        foreach (['squad' => 'squads', 'platoon' => 'platoons'] as $type => $table) {
            DB::table('units')
                ->where('legacy_type', $type)
                ->whereNotExists(fn ($query) => $query->from($table)->whereColumn("{$table}.id", 'units.legacy_id'))
                ->delete();
        }
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

    private function assignMembers(): void
    {
        DB::table('members')->whereNotNull('unit_id')->update(['unit_id' => null]);

        DB::table('members as m')
            ->join('units as squad', fn ($join) => $join->on('squad.legacy_id', '=', 'm.squad_id')->where('squad.legacy_type', 'squad'))
            ->join('units as platoon', 'platoon.id', '=', 'squad.parent_id')
            ->where('m.squad_id', '>', 0)
            ->whereColumn('platoon.legacy_id', 'm.platoon_id')
            ->whereColumn('squad.division_id', 'm.division_id')
            ->whereNull('squad.deleted_at')
            ->update(['m.unit_id' => DB::raw('squad.id')]);

        DB::table('members as m')
            ->join('units as platoon', fn ($join) => $join->on('platoon.legacy_id', '=', 'm.platoon_id')->where('platoon.legacy_type', 'platoon'))
            ->where('m.platoon_id', '>', 0)
            ->where('m.squad_id', 0)
            ->whereColumn('platoon.division_id', 'm.division_id')
            ->whereNull('platoon.deleted_at')
            ->update(['m.unit_id' => DB::raw('platoon.id')]);
    }
};
