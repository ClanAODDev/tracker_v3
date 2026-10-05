<?php

namespace App\Policies;

use App\Enums\Ability;
use App\Models\Squad;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class SquadPolicy.
 */
class SquadPolicy
{
    use HandlesAuthorization;

    /**
     * @return bool
     */
    public function before(User $user)
    {
        if ($user->can(Ability::ManageAllUnits)
            || $user->isDeveloper()
        ) {
            return true;
        }
    }

    public static function viewAny(User $user): bool
    {
        return $user->can(Ability::ViewUnits);
    }

    public static function deleteAny(User $user): bool
    {
        return $user->can(Ability::ManageUnits) || $user->isDivisionLeader();
    }

    public static function delete(User $user, Squad $squad): bool
    {
        if ($user->can(Ability::ManageUnits) || $user->isDivisionLeader()) {
            return true;
        }

        if ($user->isPlatoonLeader() && $squad->platoon->leader_id === $user->member->clan_id) {
            return true;
        }

        return false;
    }

    public static function update(User $user, Squad $squad): bool
    {
        if ($user->can(Ability::ManageUnits) && $user->member->division_id === $squad->division->id) {
            return true;
        }

        if ($user->isDivisionLeader() && $user->member->division_id === $squad->division->id) {
            return true;
        }

        if ($user->isPlatoonLeader() && $squad->platoon->leader_id === $user->member->clan_id) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function create(User $user)
    {
        if ($user->can(Ability::ManageUnits)) {
            return true;
        }

        return false;
    }
}
