<?php

namespace App\Policies;

use App\Enums\Rank;
use App\Models\Division;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        // MSgts, SGTs, developers have access to all members
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }
    }

    public function recruit(User $user): bool
    {
        // member role cannot recruit members
        if ($user->role_id > 1) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // member creation only happens through the recruitment process
        return false;
    }

    /**
     * Can the user update the given member?
     */
    public function update(User $user, Member $member): bool
    {
        if ($member->id === auth()->user()->member_id) {
            return false;
        }

        return auth()->user()->isRole('sr_ldr');
    }

    public function updateLeave(User $user, Member $member)
    {
        // can't edit yourself
        if ($member->id === auth()->user()->member_id) {
            return false;
        }

        return auth()->user()->isRole('sr_ldr');
    }

    public function view()
    {
        return true;
    }

    public function viewAny()
    {
        return true;
    }

    public function delete(): bool
    {
        // deletion not possible, only removal from AOD
        return false;
    }

    /**
     * Separate member from the clan.
     */
    public function separate(User $user, Member $member): bool
    {
        if ($member->id === $user->member->id) {
            return false;
        }

        if ($member->rank->value < $user->member->rank->value) {
            return true;
        }

        return false;
    }

    public function managePartTime(User $user, Member $member)
    {
        // can edit yourself
        if ($member->id === auth()->user()->member_id) {
            return true;
        }

        if ($user->isRole(['officer','sr_ldr'])) {
            return true;
        }

        return false;
    }

    public function promote(User $userPromoting, Member $memberBeingPromoted)
    {
        // only admin, sr_ldr, officer can promote
        if (! $userPromoting->isRole('officer')) {
            return false;
        }

        // can only promote up to one below your rank
        $rankAllowed = $userPromoting->member->rank->value - 1;
        if ($rankAllowed < $memberBeingPromoted->rank->value) {
            return false;
        }

        // can only promote within division
        if ($userPromoting->member->division_id !== $memberBeingPromoted->division_id) {
            return false;
        }

        return true;
    }

    public function manageIngameHandles(User $user, Member $member)
    {
        // can edit yourself
        if ($member->id === auth()->user()->member_id) {
            return true;
        }

        if ($user->isRole(['officer', 'sr_ldr'])) {
            return true;
        }

        return false;
    }
}
