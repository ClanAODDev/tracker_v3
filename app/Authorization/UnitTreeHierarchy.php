<?php

namespace App\Authorization;

use App\Enums\Position;
use App\Enums\UnitLevel;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\Unit;
use App\Models\User;
use App\Services\Units\UnitAssignment;
use Illuminate\Database\Eloquent\Builder;

class UnitTreeHierarchy implements UnitHierarchy
{
    public function __construct(private readonly UnitAssignment $units) {}

    public function leadershipLevel(User $user): ?UnitLevel
    {
        return match ($user->member?->position) {
            Position::PLATOON_LEADER => UnitLevel::Platoon,
            Position::SQUAD_LEADER   => UnitLevel::Squad,
            default                  => null,
        };
    }

    public function leads(Member $leader, Platoon|Squad|Unit $unit): bool
    {
        $unit = $unit instanceof Unit ? $unit : $this->units->forLegacy($unit);

        return $unit->leader_id !== null && (int) $unit->leader_id === (int) $leader->clan_id;
    }

    public function leadsUnitOf(Member $leader, Member $member): bool
    {
        for ($unit = $member->unit; $unit !== null; $unit = $unit->parent) {
            if ($this->leads($leader, $unit)) {
                return true;
            }
        }

        return false;
    }

    public function sharesLedUnit(Member $leader, Member $member, UnitLevel $level): bool
    {
        return $this->unitAt($leader, $level)?->id === $this->unitAt($member, $level)?->id;
    }

    public function scopeToLedUnit(Builder $members, Member $leader, UnitLevel $level): Builder
    {
        $unit = $this->unitAt($leader, $level);

        if ($unit !== null) {
            return $members->whereIn('unit_id', Unit::query()->where('path', 'like', $unit->path . '%')->select('id'));
        }

        return $members->where(fn (Builder $query) => $query
            ->whereNull('unit_id')
            ->orWhereIn('unit_id', Unit::query()->where('depth', '<', $level->value)->select('id')));
    }

    private function unitAt(Member $member, UnitLevel $level): ?Unit
    {
        $unit = $member->unit;

        while ($unit !== null && $unit->depth > $level->value) {
            $unit = $unit->parent;
        }

        return $unit?->depth === $level->value ? $unit : null;
    }
}
