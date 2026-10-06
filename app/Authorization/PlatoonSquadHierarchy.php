<?php

namespace App\Authorization;

use App\Enums\Position;
use App\Enums\UnitLevel;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PlatoonSquadHierarchy implements UnitHierarchy
{
    public function leadershipLevel(User $user): ?UnitLevel
    {
        return match ($user->member?->position) {
            Position::PLATOON_LEADER => UnitLevel::Platoon,
            Position::SQUAD_LEADER   => UnitLevel::Squad,
            default                  => null,
        };
    }

    public function leads(Member $leader, Platoon|Squad $unit): bool
    {
        return $unit->leader_id !== null && (int) $unit->leader_id === (int) $leader->clan_id;
    }

    public function leadsUnitOf(Member $leader, Member $member): bool
    {
        if ($member->squad_id && $member->squad && $this->leads($leader, $member->squad)) {
            return true;
        }

        return $member->platoon_id && $member->platoon && $this->leads($leader, $member->platoon);
    }

    public function sharesLedUnit(Member $leader, Member $member, UnitLevel $level): bool
    {
        $column = $this->column($level);

        return (int) $member->{$column} === (int) $leader->{$column};
    }

    public function scopeToLedUnit(Builder $members, Member $leader, UnitLevel $level): Builder
    {
        $column = $this->column($level);

        return $members->where($column, $leader->{$column});
    }

    private function column(UnitLevel $level): string
    {
        return match ($level) {
            UnitLevel::Platoon => 'platoon_id',
            UnitLevel::Squad   => 'squad_id',
        };
    }
}
