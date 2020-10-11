<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\Squad;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class SquadPolicy
 *
 * @package App\Policies
 */
class SquadPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function before(User $user)
    {
        if ($user->isRole(['admin', 'sr_ldr'])
            || $user->isDeveloper()
        ) {
            return true;
        }
    }

    /**
     * @param User $user
     * @param Squad $squad
     * @return bool
     */
    public function update(User $user, Squad $squad)
    {
        return false;
    }

    /**
     * @param User $user
     * @param Squad $squad
     * @return bool
     */
    public function delete(User $user, Squad $squad)
    {
        return false;
        // mimic create permissions
        // return $this->create($user, $squad->division);
    }

    /**
     * @param User $user
     * @param Division $division
     * @return bool
     */
    public function create(User $user, Division $division)
    {
        return false;
    }
}
