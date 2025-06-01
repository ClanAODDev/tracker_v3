<?php

namespace App\Policies;

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
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }
    }

    public function update(User $user, Platoon $platoon): bool
    {
        $member = $user->member;

        if ($user->isRole('sr_ldr') || $user->isDivisionLeader()) {
            return $platoon->division_id === $member->division_id;
        }

        if ($user->isPlatoonLeader() && $member->clan_id == $platoon->leader_id) {
            return $platoon->division_id === $member->division_id;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function delete(User $user, Platoon $platoon)
    {
        if (auth()->user()->isRole('sr_ldr') || auth()->user()->isDivisionLeader()) {
            return $platoon->division_id === auth()->user()->member->division_id;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function create(User $user)
    {
        if ($user->isRole('sr_ldr')) {
            return true;
        }

        return false;
    }
}
