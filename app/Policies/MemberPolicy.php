<?php

namespace App\Policies;

use App\User;
use App\Squad;
use App\Member;
use App\Platoon;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberPolicy
{
    use HandlesAuthorization;

    /**
     * Admins and developers have access to all members
     *
     * @param $user
     * @return bool
     */
    public function before(User $user)
    {
        return $user->isRole('admin') || $user->isDeveloper();
    }

    /**
     * Can the user update the given member?
     *
     * @TODO: Provide a mechanism for divisions to configure this policy
     *
     * @param User $user
     * @param Member $member
     * @return bool
     */
    public function update(User $user, Member $member)
    {
        if ($user->member->isDivisionLeader($member->primaryDivision)) {
            return true;
        }

        if ($member->platoon instanceof Platoon &&
            $user->member->isPlatoonLeader($member->platoon)
        ) {
            return true;
        }

        if ($member->squad instanceof Squad &&
            $user->member->isSquadLeader($member->squad)
        ) {
            return true;
        }

        return false;
    }
}
