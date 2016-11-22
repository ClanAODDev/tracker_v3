<?php

namespace App\Policies;

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

    public function update(User $user, Platoon $platoon)
    {
        if ($user->member->isDivisionLeader($platoon->division) &&
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
