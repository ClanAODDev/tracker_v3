<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\Platoon;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class PlatoonPolicy.
 */
class PlatoonPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return bool
     */
    public function before(User $user)
    {
        if ($user->isRole(['admin', 'sr_ldr']) || $user->isDeveloper()) {
            return true;
        }
    }

    /**
     * @param User    $user
     * @param Platoon $platoon
     *
     * @return bool
     */
    public function update()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function delete(User $user, Platoon $platoon)
    {
        // only allow sr_ldrs and above to delete platoons
        return false;
    }

    /**
     * @return bool
     */
    public function create(User $user, Division $division)
    {
        return false;
    }
}
