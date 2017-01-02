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


        return true;
    }

    public function update(User $user, Platoon $platoon)
    {
        return false;
    }

    public function delete(User $user, Platoon $platoon)
    {
        return $this->update($user, $platoon);
    }
}
