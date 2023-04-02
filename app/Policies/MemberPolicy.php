<?php

namespace App\Policies;

use App\Enums\Position;
use App\Models\Division;
use App\Models\Member;
use App\Models\Rank;
use App\Models\User;
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
     * @return bool
     */
    public function create(User $user): bool
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
     * @return bool
     */
    public function update(User $user, Member $member): bool
    {
        // can edit yourself
        if ($member->id === auth()->user()->member_id) {
            return false;
        }

        $userDivision = $user->member->division;
        $memberDivision = $member->division;

        // officers can update anyone within division
        if ($memberDivision instanceof Division) {
            if ($user->isRole('officer') && $userDivision->id === $memberDivision->id) {
                return true;
            }
        }

        return false;
    }

    public function reset(User $user): bool
    {
        return false;
    }

    public function view(): bool
    {
        return true;
    }

    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determines policy for removing members.
     *
     * @return bool
     */
    public function delete(User $user, Member $member): bool
    {
        // can't delete yourself
        if ($member->id === $user->member->id) {
            return false;
        }

        // use the abbreviation in case id changes for some reason
        if ($user->member->rank_id < Rank::whereAbbreviation('sgt')->first()->id) {
            return false;
        }

        return true;
    }

    public function recommend(User $actor, Member $target): bool
    {
        if (in_array($actor->position, [
            Position::COMMANDING_OFFICER,
            Position::EXECUTIVE_OFFICER,
        ])) {
            return true;
        }

        if ($actor->position === Position::PLATOON_LEADER
            // platoon leader of same platoon?
            && $actor->member->platoon_id === $target->platoon_id
        ) {
            return true;
        }

        if ($actor->position === Position::SQUAD_LEADER
            // squad leader of same squad?
            && $actor->member->squad_id === $target->squad_id
        ) {
            return true;
        }

        return false;
    }

    public function managePartTime(User $user, Member $member): bool
    {
        // can edit yourself
        if ($member->id === auth()->user()->member_id) {
            return true;
        }

        if ($user->isRole('officer')) {
            return true;
        }

        return false;
    }

    public function promote(User $userPromoting, Member $memberBeingPromoted): bool
    {
        // only admin, sr_ldr, officer can promote
        if (!$userPromoting->isRole('officer')) {
            return false;
        }

        // can only promote up to one below your rank
        $rankAllowed = $userPromoting->member->rank_id - 1;
        if ($rankAllowed < $memberBeingPromoted->rank_id) {
            return false;
        }

        // can only promote within division
        if ($userPromoting->member->division_id !== $memberBeingPromoted->division_id) {
            return false;
        }

        return true;
    }

    public function manageIngameHandles(User $user, Member $member): bool
    {
        // can edit yourself
        if ($member->id === auth()->user()->member_id) {
            return true;
        }

        if ($user->isRole('officer')) {
            return true;
        }

        return false;
    }
}
