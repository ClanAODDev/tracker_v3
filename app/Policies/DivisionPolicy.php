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
        if ($user->isRole('admin')) {
            return true;
        }

        if ($user->member->isDivisionLeader($division)
            && $user->isRole('sr_ldr')
        ) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Division $division)
    {
        return false;
    }

    public function show(User $user)
    {
        if ($user->isRole('admin')) {
            return true;
        }

        return false;
    }

    public function viewAny(User $user): bool
    {
        $division = $user->member->division;

        if ($user->isRole('admin')) {
            return true;
        }

        if (auth()->user()->isDivisionLeader()) {
            return $user->member->division_id === $division->id;
        }

        return false;
    }

    public function view(User $user, Division $division)
    {
        if ($user->isRole('admin')) {
            return true;
        }

        if ($user->member->division_id === $division->id) {
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
