<?php

namespace App\Policies;

use App\User;
use App\Rank;
use App\Squad;
use App\Member;
use App\Platoon;
use App\Division;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberPolicy
{
    use HandlesAuthorization;

    /**
     * Admins and developers have carte blanche access
     *
     * @param $user
     * @return bool
     */
    public function before(User $user)
    {
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }
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
        // division leaders can modify anyone in their own division
        if ($member->primaryDivision instanceof Division &&
            $user->member->isDivisionLeader($member->primaryDivision) &&
            $user->isRole('sr_ldr')
        ) {
            return true;
        }

        // platoon leaders can modify anyone in their own platoon
        if ($member->platoon instanceof Platoon &&
            $user->isRole('sr_ldr') &&
            $user->member->isPlatoonLeader($member->platoon)
        ) {
            return true;
        }

        if ($member->squad instanceof Squad &&
            $user->isRole('jr_ldr') &&
            $user->member->isSquadLeader($member->squad)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determines policy for removing members
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user)
    {
        // use the abbreviation in case id changes for some reason
        $minimumRankToRemove = Rank::whereAbbreviation('sgt')->first();

        return $user->member->rank_id >= $minimumRankToRemove->id;
    }
}
