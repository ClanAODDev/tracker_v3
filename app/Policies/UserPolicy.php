<?php

namespace App\Policies;

use App\Role;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param User $user
     * @return bool
     */
    public function before(User $user)
    {
        if ($user->isRole(['admin'])
            || $user->isDeveloper()
        ) {
            return true;
        }
    }

    /**
     * @param $role_id
     * @return bool
     */
    public function update(User $user, $role_id)
    {
        // you can only give access less than your own
        if ($role_id >= auth()->user()->role->id) {
            return false;
        }

        // only SGT+ can give role adjustments
        return auth()->user()->member->isRank(['sgt', 'ssgt'])
            && auth()->user()->isRole('sr_ldr');
    }
}
