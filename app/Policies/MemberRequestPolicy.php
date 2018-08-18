<?php

namespace App\Policies;

use App\MemberRequest;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberRequestPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function manage(User $user)
    {
        if ($user->isRole('admin')) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, MemberRequest $request) {
        if ($user->isRole(['sr_ldr', 'admin'])) {
            return true;
        }

        if ($request->requester_id === $user->member->clan_id) {
            return true;
        }

        return false;
    }
}
