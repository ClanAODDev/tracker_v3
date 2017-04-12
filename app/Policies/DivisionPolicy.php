<?php

namespace App\Policies;

use App\Division;
use App\User;
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

    /**
     * @param Division $division
     * @return bool
     */
    public function show(Division $division)
    {
        if ( ! $division->active) {
            return false;
        }

        return true;
    }

    /**
     * @param User $user
     * @param Division $division
     * @return bool
     */
    public function update(User $user, Division $division)
    {

        /**
         * is division active?
         * is the user a division leader of the division?
         * is the user a senior leader?
         */

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
