<?php

namespace App\Services\Units;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Division;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class UnitAssignment
{
    private const PLATOON_COLUMNS = ['name', 'description', 'logo', 'order', 'leader_id'];

    private const SQUAD_COLUMNS = ['name', 'logo', 'gen_pop', 'leader_id'];

    private const UNIT_COLUMNS = ['name', 'description', 'logo', 'order', 'gen_pop', 'leader_id'];

    public function forLegacy(Platoon|Squad $legacy): Unit
    {
        return Unit::withTrashed()
            ->where('legacy_type', $legacy instanceof Platoon ? Unit::LEGACY_PLATOON : Unit::LEGACY_SQUAD)
            ->where('legacy_id', $legacy->getKey())
            ->firstOr(fn () => throw new LogicException('No unit for ' . class_basename($legacy) . " {$legacy->getKey()}; run tracker:units-sync."));
    }

    public function forLegacyIds(?int $platoonId, ?int $squadId): ?Unit
    {
        if ($squadId) {
            return $this->forLegacy(Squad::withTrashed()->findOrFail($squadId));
        }

        if ($platoonId) {
            return $this->forLegacy(Platoon::withTrashed()->findOrFail($platoonId));
        }

        return null;
    }

    public function columnsFor(?Unit $unit): array
    {
        if ($unit === null) {
            return ['unit_id' => null, 'platoon_id' => 0, 'squad_id' => 0];
        }

        return match (true) {
            $unit->isSquad()   => ['unit_id' => $unit->id, 'platoon_id' => (int) $unit->parent?->legacy_id, 'squad_id' => $unit->legacy_id],
            $unit->isPlatoon() => ['unit_id' => $unit->id, 'platoon_id' => $unit->legacy_id, 'squad_id' => 0],
            default            => throw new LogicException("Unit {$unit->id} has no platoon or squad to write back to."),
        };
    }

    public function create(Division $division, ?Unit $parent, array $attributes): Unit
    {
        if ($parent?->depth >= 2) {
            throw new InvalidArgumentException('Units can only be two levels deep while platoons and squads are still written.');
        }

        return DB::transaction(function () use ($division, $parent, $attributes) {
            $now = now();

            if ($parent === null) {
                $legacyId = DB::table('platoons')->insertGetId([
                    ...array_intersect_key($attributes, array_flip(self::PLATOON_COLUMNS)),
                    'division_id' => $division->id,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            } else {
                $legacyId = DB::table('squads')->insertGetId([
                    ...array_intersect_key($attributes, array_flip(self::SQUAD_COLUMNS)),
                    'platoon_id' => $parent->legacy_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $unit = Unit::create([
                ...array_intersect_key($attributes, array_flip(['name', 'description', 'logo', 'order', 'gen_pop'])),
                'division_id' => $division->id,
                'parent_id'   => $parent?->id,
                'depth'       => $parent ? 2 : 1,
                'legacy_type' => $parent ? Unit::LEGACY_SQUAD : Unit::LEGACY_PLATOON,
                'legacy_id'   => $legacyId,
            ]);

            $unit->update(['path' => ($parent?->path ?? '/') . $unit->id . '/']);
            $this->recordActivity($unit, $parent ? ActivityType::CREATED_SQUAD : ActivityType::CREATED_PLATOON);

            return $unit;
        });
    }

    public function update(Unit $unit, array $attributes): Unit
    {
        if (array_key_exists('leader_id', $attributes)) {
            $attributes['leader_id'] = ((int) $attributes['leader_id']) ?: null;
        }

        DB::transaction(function () use ($unit, $attributes) {
            $unit->update(array_intersect_key($attributes, array_flip(self::UNIT_COLUMNS)));
            $this->writeBack($unit, array_intersect_key($attributes, array_flip($unit->isPlatoon() ? self::PLATOON_COLUMNS : self::SQUAD_COLUMNS)));
            $this->recordActivity($unit, $unit->isPlatoon() ? ActivityType::UPDATED_PLATOON : ActivityType::UPDATED_SQUAD);
        });

        return $unit;
    }

    public function archive(Unit $unit, bool $recordActivity = true): void
    {
        DB::transaction(function () use ($unit, $recordActivity) {
            $unit->delete();
            $this->writeBack($unit, ['deleted_at' => $unit->deleted_at]);

            if ($recordActivity) {
                $this->recordActivity($unit, $unit->isPlatoon() ? ActivityType::DELETED_PLATOON : ActivityType::DELETED_SQUAD);
            }
        });
    }

    public function restore(Unit $unit): void
    {
        DB::transaction(function () use ($unit) {
            $unit->restore();
            $this->writeBack($unit, ['deleted_at' => null]);
        });
    }

    public function setLeader(Unit $unit, ?int $clanId, bool $recordActivity = true): void
    {
        DB::transaction(function () use ($unit, $clanId, $recordActivity) {
            $unit->update(['leader_id' => $clanId ?: null]);
            $this->writeBack($unit, ['leader_id' => $clanId ?: null]);

            if ($recordActivity) {
                $this->recordActivity($unit, $unit->isPlatoon() ? ActivityType::UPDATED_PLATOON : ActivityType::UPDATED_SQUAD);
            }
        });
    }

    public function clearLeadership(array $clanIds, ?Unit $except = null): int
    {
        $clanIds = array_values(array_filter($clanIds));

        if ($clanIds === []) {
            return 0;
        }

        return DB::transaction(function () use ($clanIds, $except) {
            $units = Unit::query()
                ->whereIn('leader_id', $clanIds)
                ->when($except, fn ($query) => $query->whereKeyNot($except->id))
                ->get();

            foreach ($units as $unit) {
                $unit->update(['leader_id' => null]);
                $this->writeBack($unit, ['leader_id' => null]);
            }

            return $units->count();
        });
    }

    private function writeBack(Unit $unit, array $columns): void
    {
        if ($columns === []) {
            return;
        }

        DB::table($unit->isPlatoon() ? 'platoons' : 'squads')
            ->where('id', $unit->legacy_id)
            ->update([...$columns, 'updated_at' => now()]);
    }

    private function recordActivity(Unit $unit, ActivityType $type): void
    {
        if (! auth()->check()) {
            return;
        }

        $actor = auth()->user();

        Activity::create([
            'name'         => $type,
            'user_id'      => $actor->id,
            'subject_id'   => $unit->id,
            'subject_type' => Unit::class,
            'division_id'  => $unit->division_id ?? $actor->member?->division_id,
            'properties'   => null,
        ]);
    }
}
