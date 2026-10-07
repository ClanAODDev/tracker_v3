<?php

namespace App\Policies;

use App\Authorization\UnitHierarchy;
use App\Enums\Ability;
use App\Enums\UnitLevel;
use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function before(User $user)
    {
        if ($user->can(Ability::ManageAllUnits) || $user->isDeveloper()) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->can(Ability::ViewUnits);
    }

    public function create(User $user, $division = null): bool
    {
        if (! $user->can(Ability::ManageUnits)) {
            return false;
        }

        if ($division) {
            return $user->member->division_id === $division->id;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Ability::ManageUnits) || $user->isDivisionLeader();
    }

    public function delete(User $user, Unit $unit): bool
    {
        $units = app(UnitHierarchy::class);

        if ($unit->isPlatoon()) {
            if ($user->can(Ability::ManageUnits) || $user->isDivisionLeader()) {
                return $unit->division_id === $user->member->division_id;
            }

            return false;
        }

        if ($user->can(Ability::ManageUnits) || $user->isDivisionLeader()) {
            return true;
        }

        return $unit->parent !== null
            && $units->leadershipLevel($user) === UnitLevel::Platoon
            && $units->leads($user->member, $unit->parent);
    }

    public function update(User $user, Unit $unit): bool
    {
        $member = $user->member;
        $units  = app(UnitHierarchy::class);

        if ($unit->isPlatoon()) {
            if ($user->can(Ability::ManageUnits) || $user->isDivisionLeader()) {
                return $unit->division_id === $member->division_id;
            }

            if ($units->leadershipLevel($user) === UnitLevel::Platoon && $units->leads($member, $unit)) {
                return $unit->division_id === $member->division_id;
            }

            return false;
        }

        if ($user->can(Ability::ManageUnits) && $member->division_id === $unit->division_id) {
            return true;
        }

        if ($user->isDivisionLeader() && $member->division_id === $unit->division_id) {
            return true;
        }

        return $unit->parent !== null
            && $units->leadershipLevel($user) === UnitLevel::Platoon
            && $units->leads($member, $unit->parent);
    }
}
