<?php

namespace App\Policies;

use App\MemberRequest;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberRequestPolicy
{
    use HandlesAuthorization;

    public function before()
    {
        if (auth()->user()->isDeveloper()) {
            return true;
        }
    }

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

    /**
     * @param User $user
     * @param MemberRequest $memberRequest
     * @return bool
     */
    public function edit(User $user, MemberRequest $memberRequest)
    {
        if ($memberRequest->isApproved() || !$memberRequest->isCancelled()) {
            return false;
        }

        if ($user->isRole(['sr_ldr', 'admin'])) {
            return true;
        }

        if ($memberRequest->requester_id === $user->member->clan_id) {
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param MemberRequest $request
     * @return bool
     */
    public function cancel(User $user, MemberRequest $request)
    {
        if ($user->isRole(['sr_ldr', 'admin'])) {
            return true;
        }

        if ($request->requester_id === $user->member->clan_id) {
            return true;
        }

        return false;
    }
}
