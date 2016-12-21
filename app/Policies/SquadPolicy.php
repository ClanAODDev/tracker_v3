<?php

namespace App\Policies;

use App\Platoon;
use App\User;
use App\Squad;
use Illuminate\Auth\Access\HandlesAuthorization;

class SquadPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }
    }

    public function update(User $user, Squad $squad)
    {
        // is user the division leader of division containing squad?
        if ($user->member->isDivisionLeader($squad->platoon->division) &&
            $user->isRole('sr_ldr')
        ) {
            return true;
        }

        // is user the platoon leader of platoon containing squad?
        if ($user->member->platoon instanceof Platoon &&
            $user->isRole('sr_ldr') &&
            $user->member->platoon->squads->contains($squad)
        ) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Squad $squad)
    {
        // delete shares policies with update
        return $this->update($user, $squad);
    }

    public function create(User $user)
    {
        // is user the division leader of division containing squad?
        if ($user->isRole('sr_ldr')) {
            return true;
        }

        return false;
    }
}
