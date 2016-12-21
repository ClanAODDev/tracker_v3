<?php

namespace App\Policies;

use App\Division;
use App\User;
use App\Platoon;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlatoonPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @param User $user
     * @return bool
     */
    public function before(User $user)
    {
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }
    }

    public function create(User $user, Division $division)
    {
        if ( ! $user->isRole('sr_ldr')) {
            return false;
        }

        // @TODO: Is platoon being created in primary division?
        // Admins and developers can create platoons in any division
        // and thus this condition will only impact sr leaders

        return true;
    }

    public function update(User $user, Platoon $platoon)
    {
        if ($user->member->isDivisionLeader($platoon->division) &&
            $user->isRole('sr_ldr')
        ) {
            return true;
        }

        if ($user->member->isPlatoonLeader($platoon) &&
            $user->isRole('sr_ldr')
        ) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Platoon $platoon)
    {
        return $this->update($user, $platoon);
    }
}
