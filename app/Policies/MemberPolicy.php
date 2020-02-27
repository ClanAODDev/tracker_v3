<?php

namespace App\Policies;

use App\Division;
use App\Member;
use App\Rank;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        // MSgts, SGTs, developers have access to all members
        if ($user->isRole(['admin', 'sr_ldr']) || $user->isDeveloper()) {
            return true;
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        // member role cannot recruit members
        if ($user->role_id > 1) {
            return true;
        }

        return false;
    }

    /**
     * Can the user update the given member?
     *
     * @param User $user
     * @param Member $member
     * @return bool
     */
    public function update(User $user, Member $member)
    {
        // can edit yourself
        if ($member->id === auth()->user()->member_id) {
            return false;
        }

        $userDivision = $user->member->division;
        $memberDivision = $member->division;

        // Jr leaders (CPl), and officers can update anyone within division
        if ($memberDivision instanceof Division) {
            if ($user->isRole(['jr_ldr', 'officer']) && $userDivision->id == $memberDivision->id) {
                return true;
            }
        }

        return false;
    }

    public function reset(User $user)
    {
        return false;
    }

    public function view()
    {
        return true;
    }

    public function viewAny()
    {
        return true;
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

        // prevent exploiting ability to change rank to SGT
        if (!$user->isRole(['admin', 'sr_ldr'])) {
            return false;
        }

        // use the abbreviation in case id changes for some reason
        if ($user->member->rank_id < Rank::whereAbbreviation('sgt')->first()->id) {
            return false;
        }

        return true;
    }

    public function managePartTime(User $user, Member $member)
    {
        // can edit yourself
        if ($member->id === auth()->user()->member_id) {
            return true;
        }

        if ($user->isRole('jr_ldr')) {
            return true;
        }

        if ($user->isRole('officer')) {
            if ($member->platoon && $user->member->platoon) {
                return $member->platoon_id == $user->member->platoon_id;
            }
        }

        return false;
    }

    public function manageIngameHandles(User $user, Member $member)
    {
        // can edit yourself
        if ($member->id === auth()->user()->member_id) {
            return true;
        }

        if ($user->isRole('jr_ldr')) {
            return true;
        }

        if ($user->isRole('officer')) {
            if ($member->platoon && $user->member->platoon) {
                return $member->platoon_id == $user->member->platoon_id;
            }
        }

        return false;
    }
}
