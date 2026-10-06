<?php

namespace App\Policies;

use App\Authorization\UnitHierarchy;
use App\Enums\Ability;
use App\Enums\UnitLevel;
use App\Models\Platoon;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class PlatoonPolicy.
 */
class PlatoonPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return bool
     */
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

    public function update(User $user, Platoon $platoon): bool
    {
        $member = $user->member;

        if ($user->can(Ability::ManageUnits) || $user->isDivisionLeader()) {
            return $platoon->division_id === $member->division_id;
        }

        if (app(UnitHierarchy::class)->leadershipLevel($user) === UnitLevel::Platoon && app(UnitHierarchy::class)->leads($member, $platoon)) {
            return $platoon->division_id === $member->division_id;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function delete(User $user, Platoon $platoon)
    {
        if (auth()->user()->can(Ability::ManageUnits) || auth()->user()->isDivisionLeader()) {
            return $platoon->division_id === auth()->user()->member->division_id;
        }

        return false;
    }

    public function create(User $user, $division = null): bool
    {
        if (! $user->can(Ability::ManageUnits)) {
            return false;
        }

        if ($division && $user->member) {
            return $user->member->division_id === $division->id;
        }

        return true;
    }
}
