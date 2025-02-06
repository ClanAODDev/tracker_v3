<?php

namespace App\Policies;

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
        if ($user->isRole(['admin']) || $user->isDeveloper()) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        $division = $user->member->division;

        if ($user->member->division_id === $division->id) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function update(User $user, Platoon $platoon)
    {
        if ($user->member->division_id === $platoon->division->id && !$user->isRole(['officer', 'member'])) {
            return true;
        }
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
    public function create(User $user)
    {
        if ($user->isRole(['sr_ldr'])) {
            return true;
        }

        return false;
    }
}
