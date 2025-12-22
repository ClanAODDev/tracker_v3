<?php

namespace App\Policies;

use App\Models\DivisionTag;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
        if (! $user->isRole('sr_ldr') && ! $user->isPlatoonLeader() && ! $user->isSquadLeader()) {
            return false;
        }

        if ($member === null) {
            return true;
        }

        $userMember = $user->member;
        if (! $userMember || ! $userMember->rank) {
            return false;
        }

        if (! $member->rank) {
            return true;
        }

        return $userMember->rank->value > $member->rank->value;
    }
}
