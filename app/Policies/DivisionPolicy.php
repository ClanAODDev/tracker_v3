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
        if ($user->isDeveloper()) {
            return true;
        }
    }

    /**
     * @param User $user
     * @param Division $division
     * @return bool
     */
    public function update(User $user, Division $division)
    {
        /**
         * is the user a division leader of the division?
         * is the user a senior leader?
         * is the user a SGT in the division?
         */

        if ($user->isRole('admin')) {
            return true;
        }

        if ($user->member->isDivisionLeader($division) &&
            $user->isRole('sr_ldr')
        ) {
            return true;
        }

        if ($user->member->division->id == $division->id
            && $user->isRole('sr_ldr')
            && $user->member->isRank(['Sgt', 'SSgt'])
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

    public function show(User $user)
    {
        if ($user->isRole('admin')) {
            return true;
        }

        return false;
    }

    public function create(User $user)
    {
        if ($user->isRole('admin')) {
            return true;
        }

        return false;
    }
}
