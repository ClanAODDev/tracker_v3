<?php

namespace App\Policies;

use App\User;
use App\Division;
use Illuminate\Auth\Access\HandlesAuthorization;

class DivisionPolicy
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

    public function update(User $user, Division $division)
    {
        if ( ! $division->active) {
            return false;
        }

        if ($user->member->isDivisionLeader($division) &&
            $user->isRole('sr_ldr')
        ) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Division $division)
    {
        if ($user->member->isDivisionLeader($division) &&
            $user->isRole('sr_ldr')
        ) {
            return true;
        }

        return false;
    }
}
