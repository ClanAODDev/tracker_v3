<?php

namespace App\Policies;

use App\Enums\Rank;
use App\Enums\Role;
use App\Models\DivisionTag;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Builder;

class DivisionTagPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DivisionTag $tag): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isRole('sr_ldr');
    }

    public function update(User $user, DivisionTag $tag): bool
    {
        if ($tag->division_id === null) {
            return false;
        }

        if (! $user->isRole('sr_ldr')) {
            return false;
        }

        return $user->member->division_id === $tag->division_id;
    }

    public function delete(User $user, DivisionTag $tag): bool
    {
        if ($tag->division_id === null) {
            return false;
        }

        if (! $user->isRole('sr_ldr')) {
            return false;
        }

        return $user->member->division_id === $tag->division_id;
    }

    public function assign(User $user, ?Member $member = null): bool
    {
        $userMember = $user->member;

        if (! $userMember) {
            return false;
        }

        $isSgt     = $userMember->rank->value >= Rank::SERGEANT->value;
        $isMsgt    = $userMember->rank->value >= Rank::MASTER_SERGEANT->value;
        $isOfficer = $user->isRole([Role::OFFICER, Role::SENIOR_LEADER]);

        if (! $isOfficer && ! $isSgt) {
            return false;
        }

        if ($member === null) {
            return true;
        }

        if (! $member->division_id) {
            return $isMsgt;
        }

        if ($isSgt) {
            return true;
        }

        return $userMember->division_id === $member->division_id;
    }

    public function getAssignableTags(User $user, Member $member): Builder
    {
        if ($user->isRole('admin')) {
            return DivisionTag::query()->visibleTo($user)->orderBy('name');
        }

        $userMember     = $user->member;
        $userDivisionId = $userMember?->division_id;

        if (! $userDivisionId) {
            return DivisionTag::query()->whereRaw('1 = 0');
        }

        if ($userMember->rank->value >= Rank::SERGEANT->value) {
            return DivisionTag::query()->visibleTo($user)->orderBy('name');
        }

        return DivisionTag::forDivision($userDivisionId)->visibleTo($user);
    }
}
