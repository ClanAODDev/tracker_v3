<?php

namespace App\Services\Units;

use App\Authorization\UnitHierarchy;
use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Division;
use App\Models\DivisionUnitLevel;
use App\Models\Member;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UnitAssignment
{
    private const UNIT_COLUMNS = ['name', 'description', 'logo', 'order', 'gen_pop', 'leader_id'];

    public function create(Division $division, ?Unit $parent, array $attributes): Unit
    {
        $depth = ($parent?->depth ?? 0) + 1;

        if ($depth > $division->deepestUnitLevel()) {
            throw new InvalidArgumentException("{$division->name} has no level {$depth}.");
        }

        return DB::transaction(function () use ($division, $parent, $attributes, $depth) {
            $unit = Unit::create([
                ...array_intersect_key($attributes, array_flip(self::UNIT_COLUMNS)),
                'division_id' => $division->id,
                'parent_id'   => $parent?->id,
                'depth'       => $depth,
            ]);

            $unit->update(['path' => ($parent?->path ?? '/') . $unit->id . '/']);
            $this->claimLeader($unit);
            $this->recordActivity($unit, ActivityType::CREATED_UNIT);

            return $unit;
        });
    }

    public function insertTopLevel(Division $division, array $level, array $unitAttributes): Unit
    {
        $levels = $division->unitLevels()->count();

        if ($levels < 1 || $levels >= Division::MAX_UNIT_LEVELS) {
            throw new InvalidArgumentException("{$division->name} cannot add a level above its existing levels.");
        }

        return DB::transaction(function () use ($division, $level, $unitAttributes) {
            $this->pushLevelsDown($division, $level);

            $root = Unit::create([
                ...array_intersect_key($unitAttributes, array_flip(self::UNIT_COLUMNS)),
                'division_id' => $division->id,
                'parent_id'   => null,
                'depth'       => 1,
            ]);

            $root->update(['path' => '/' . $root->id . '/']);

            $this->adoptExistingUnits($division, $root);

            $division->unsetRelation('unitLevels');
            $this->flushHierarchy();
            $this->recordActivity($root, ActivityType::CREATED_UNIT);

            return $root;
        });
    }

    public function move(Unit $unit, ?Unit $newParent): void
    {
        $expectedParentDepth = $unit->depth - 1;

        if ($expectedParentDepth < 1 || $newParent === null || $newParent->division_id !== $unit->division_id || $newParent->depth !== $expectedParentDepth || $newParent->trashed()) {
            throw new InvalidArgumentException('That unit cannot be moved there.');
        }

        DB::transaction(function () use ($unit, $newParent) {
            $oldPath = $unit->path;
            $newPath = $newParent->path . $unit->id . '/';

            $unit->update(['parent_id' => $newParent->id, 'path' => $newPath]);

            $this->reprefixPaths(
                Unit::withTrashed()->where('path', 'like', $oldPath . '%')->whereKeyNot($unit->id),
                oldPrefix: $oldPath,
                newPrefix: $newPath,
            );

            $this->flushHierarchy();
            $this->recordActivity($unit, ActivityType::UPDATED_UNIT);
        });
    }

    public function update(Unit $unit, array $attributes): Unit
    {
        if (array_key_exists('leader_id', $attributes)) {
            $attributes['leader_id'] = ((int) $attributes['leader_id']) ?: null;
        }

        DB::transaction(function () use ($unit, $attributes) {
            $unit->update(array_intersect_key($attributes, array_flip(self::UNIT_COLUMNS)));
            $this->claimLeader($unit);
            $this->flushHierarchy();
            $this->recordActivity($unit, ActivityType::UPDATED_UNIT);
        });

        return $unit;
    }

    public function archive(Unit $unit, bool $recordActivity = true): void
    {
        DB::transaction(function () use ($unit, $recordActivity) {
            $unit->delete();
            $this->flushHierarchy();

            if ($recordActivity) {
                $this->recordActivity($unit, ActivityType::DELETED_UNIT);
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
            $this->claimLeader($unit);
            $this->flushHierarchy();

            if ($recordActivity) {
                $this->recordActivity($unit, ActivityType::UPDATED_UNIT);
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

    public function assignMember(Member $member, ?Unit $unit): void
    {
        DB::transaction(function () use ($member, $unit) {
            $member->update(['unit_id' => $unit?->id]);
            $this->recordAssignment($member, $unit);
        });
    }

    private function claimLeader(Unit $unit): void
    {
        if ($unit->leader_id) {
            $this->clearLeadership([$unit->leader_id], except: $unit);
        }
    }

    private function recordAssignment(Member $member, ?Unit $unit): void
    {
        if ($unit === null) {
            $member->recordActivity(ActivityType::UNASSIGNED);

            return;
        }

        if ($unit->parent_id === null) {
            $member->recordActivity(ActivityType::ASSIGNED_PLATOON, ['platoon' => $unit->name]);

            return;
        }

        $member->recordActivity(ActivityType::ASSIGNED_SQUAD, [
            'platoon' => $unit->parent->name,
            'squad'   => $unit->name,
        ]);
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

    private function pushLevelsDown(Division $division, array $topLevel): void
    {
        $division->unitLevels()->reorder('depth', 'desc')->get()
            ->each(fn (DivisionUnitLevel $existing) => $existing->update(['depth' => $existing->depth + 1]));

        $division->unitLevels()->create([
            ...array_intersect_key($topLevel, array_flip(['label', 'label_plural', 'leader_title'])),
            'depth' => 1,
        ]);

        Unit::withTrashed()->where('division_id', $division->id)->increment('depth');
    }

    private function adoptExistingUnits(Division $division, Unit $root): void
    {
        $existing = fn () => Unit::withTrashed()->where('division_id', $division->id)->whereKeyNot($root->id);

        $this->reprefixPaths($existing(), oldPrefix: '/', newPrefix: $root->path);

        $existing()->whereNull('parent_id')->update(['parent_id' => $root->id]);
    }

    private function reprefixPaths(Builder $units, string $oldPrefix, string $newPrefix): void
    {
        $units->update(['path' => DB::raw('concat(' . DB::getPdo()->quote($newPrefix) . ', substr(path, ' . (strlen($oldPrefix) + 1) . '))')]);
    }

    private function flushHierarchy(): void
    {
        app(UnitHierarchy::class)->flush();
    }
}
