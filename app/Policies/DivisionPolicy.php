<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DivisionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return bool
     */
    public function before(User $user)
    {
        if ($user->isDeveloper()) {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function update(User $user, Division $division)
    {
        /*
         * is the user a division leader of the division?
         * is the user a senior leader?
         * is the user a SGT in the division?
         */
        if ($user->isRole('administrator')) {
            return true;
        }

        if ($user->member->isDivisionLeader($division)
            && $user->isRole('senior leader')
        ) {
            return true;
        }

        if ($user->member->division->id === $division->id
            && $user->isRole('senior leader')
            && $user->member->isRank(['Sgt', 'SSgt'])
        ) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Division $division)
    {
        if ($user->member->isDivisionLeader($division)
            && $user->isRole('senior leader')
        ) {
            return true;
        }

        return false;
    }

    public function show(User $user)
    {
        if ($user->isRole('administrator')) {
            return true;
        }

        return false;
    }

    public function viewAny(User $user)
    {
        if ($user->isRole('administrator')) {
            return true;
        }

        return false;
    }

    public function view(User $user)
    {
        if ($user->isRole('administrator')) {
            return true;
        }

        return false;
    }

    public function create(User $user)
    {
        if ($user->isRole('administrator')) {
            return true;
        }

        return false;
    }
}
