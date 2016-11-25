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
        // admins and developers can update
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }

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

        // squad leaders can edit members of their squad
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
     * @param Member $member
     * @return bool
     */
    public function delete(User $user, Member $member)
    {
        // can't delete yourself
        if ($member->id == $user->member->id) {
            return false;
        }

        // use the abbreviation in case id changes for some reason
        if ($user->member->rank_id < Rank::whereAbbreviation('sgt')->first()->id) {
            return false;
        }


        return true;


    }
}
