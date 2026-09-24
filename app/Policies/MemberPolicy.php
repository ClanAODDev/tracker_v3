<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Division;
use App\Models\DivisionMemberField;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MemberPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }
    }

    public function recruit(User $user): bool
    {
        // member role cannot recruit members
        if ($user->role->value > Role::MEMBER->value) {
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

    /**
     * Can the user reset the given member's squad/platoon assignments?
     */
    public function reset(User $user, Member $member): bool
    {
        if ($member->id === auth()->user()->member_id) {
            return false;
        }

        return auth()->user()->isRole('sr_ldr');
    }

    public function flagInactive(User $user): bool
    {
        return $user->isRole(['officer', 'sr_ldr']);
    }

    public function remindActivity(User $user, ?Member $member = null): bool
    {
        if ($member && $member->id === $user->member_id) {
            return false;
        }

        return $user->isRole(['officer', 'sr_ldr']);
    }

    /**
     * Can the user clear the given member's activity reminders?
     *
     * Deliberately stricter than remindActivity(): clearing reminder
     * history is restricted to sr_ldr, unlike setting one which any
     * officer can do.
     */
    public function clearActivityReminders(User $user, Member $member): Response
    {
        if ($member->id === $user->member_id) {
            return Response::deny('Cannot clear your own reminders');
        }

        return $user->isRole('sr_ldr')
            ? Response::allow()
            : Response::deny();
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

        if (! $user->isRole('sr_ldr')) {
            return false;
        }

        return $user->member->outranks($member);
    }

    public function managePartTime(User $user, Member $member): bool
    {
        return $user->isRole(['officer', 'sr_ldr']);
    }

    public function promote(User $userPromoting, Member $memberBeingPromoted)
    {
        // only admin, sr_ldr, officer can promote
        if (! $userPromoting->isRole('officer')) {
            return false;
        }

        // can only promote up to one below your rank
        if (! $userPromoting->member->outranks($memberBeingPromoted)) {
            return false;
        }

        // can only promote within division
        if ($userPromoting->member->division_id !== $memberBeingPromoted->division_id) {
            return false;
        }

        return true;
    }

    /**
     * Can the user manage this member's in-game handles?
     *
     * Always allowed for the member themselves, plus sr_ldr for anyone, officers
     * within the member's own division, and squad/platoon leaders for members in
     * their own squad/platoon respectively.
     */
    public function manageHandles(User $user, Member $member): bool
    {
        if ($member->id === $user->member_id) {
            return true;
        }

        return $this->isLeaderOf($user, $member);
    }

    /**
     * Can the user manage AT LEAST ONE of this member's division-defined field
     * values? Used to decide whether the fields editor is reachable at all;
     * manageField() below is the actual per-field authority.
     */
    public function manageFields(User $user, Member $member): bool
    {
        if ($this->isLeaderOf($user, $member)) {
            return true;
        }

        if ($member->id !== $user->member_id) {
            return false;
        }

        return $member->division?->memberFields->contains('self_editable', true) ?? false;
    }

    /**
     * Can the user manage this specific field's value for this member?
     *
     * Leadership tiers (sr_ldr / officer-in-division / squad or platoon
     * leader) can manage any field. A member editing their own value can
     * only do so when the field itself has been marked self-editable.
     */
    public function manageField(User $user, Member $member, DivisionMemberField $field): bool
    {
        if ($this->isLeaderOf($user, $member)) {
            return true;
        }

        return $member->id === $user->member_id && $field->self_editable;
    }

    /**
     * Shared leadership check for manageHandles()/manageFields(): sr_ldr for
     * anyone, officers within the member's own division, and squad/platoon
     * leaders for members in their own squad/platoon respectively.
     */
    private function isLeaderOf(User $user, Member $member): bool
    {
        if ($user->isRole('sr_ldr')) {
            return true;
        }

        $userMember = $user->member;

        if (! $userMember) {
            return false;
        }

        if ($user->isRole('officer') && $userMember->division_id === $member->division_id) {
            return true;
        }

        if ($member->squad_id && $member->squad && $userMember->isSquadLeader($member->squad)) {
            return true;
        }

        if ($member->platoon_id && $member->platoon && $userMember->isPlatoonLeader($member->platoon)) {
            return true;
        }

        return false;
    }
}
