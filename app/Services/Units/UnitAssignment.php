<?php

namespace App\Services\Units;

use App\Authorization\UnitHierarchy;
use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Division;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UnitAssignment
{
    private const UNIT_COLUMNS = ['name', 'description', 'logo', 'order', 'gen_pop', 'leader_id'];

    public function create(Division $division, ?Unit $parent, array $attributes): Unit
    {
        return DB::transaction(function () use ($division, $parent, $attributes) {
            $unit = Unit::create([
                ...array_intersect_key($attributes, array_flip(['name', 'description', 'logo', 'order', 'gen_pop', 'leader_id'])),
                'division_id' => $division->id,
                'parent_id'   => $parent?->id,
                'depth'       => ($parent?->depth ?? 0) + 1,
            ]);

            $unit->update(['path' => ($parent?->path ?? '/') . $unit->id . '/']);
            $this->recordActivity($unit, $unit->isPlatoon() ? ActivityType::CREATED_PLATOON : ActivityType::CREATED_SQUAD);

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
            $this->flushHierarchy();
            $this->recordActivity($unit, $unit->isPlatoon() ? ActivityType::UPDATED_PLATOON : ActivityType::UPDATED_SQUAD);
        });

        return $unit;
    }

    public function archive(Unit $unit, bool $recordActivity = true): void
    {
        DB::transaction(function () use ($unit, $recordActivity) {
            $unit->delete();
            $this->flushHierarchy();

            if ($recordActivity) {
                $this->recordActivity($unit, $unit->isPlatoon() ? ActivityType::DELETED_PLATOON : ActivityType::DELETED_SQUAD);
            }
        });
    }

    public function restore(Unit $unit): void
    {
        DB::transaction(function () use ($unit) {
            $unit->restore();
            $this->flushHierarchy();
        });
    }

    public function setLeader(Unit $unit, ?int $clanId, bool $recordActivity = true): void
    {
        DB::transaction(function () use ($unit, $clanId, $recordActivity) {
            $unit->update(['leader_id' => $clanId ?: null]);
            $this->flushHierarchy();

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
            }

            $this->flushHierarchy();

            return $units->count();
        });
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

    private function flushHierarchy(): void
    {
        app(UnitHierarchy::class)->flush();
    }
}
