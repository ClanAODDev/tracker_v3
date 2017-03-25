<?php

namespace App\Policies;

use App\Division;
use App\Platoon;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class PlatoonPolicy
 * @package App\Policies
 */
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
        if ($user->isRole(['admin', 'sr_ldr']) || $user->isDeveloper()) {
            return true;
        }
    }

    /**
     * @param User $user
     * @param Platoon $platoon
     * @return bool
     */
    public function update(User $user, Platoon $platoon)
    {
        // moderators (CPLs) can affect platoons within their division
        if ($user->isRole('jr_ldr') && $user->member->primaryDivision->id == $platoon->division->id) {
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param Platoon $platoon
     * @return bool
     */
    public function delete(User $user, Platoon $platoon)
    {
        // only allow sr_ldrs and above to delete platoons
        return false;
    }

    /**
     * @param User $user
     * @param Division $division
     * @return bool
     */
    public function create(User $user, Division $division)
    {
        // CPLs can create platoons within their own division
        if ($user->isRole('jr_ldr') && $user->member->primaryDivision->id == $division->id) {
            return true;
        }
    }
}
