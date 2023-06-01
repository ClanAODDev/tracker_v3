<?php

namespace App\Policies;

use App\Models\MemberRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberRequestPolicy
{
    use HandlesAuthorization;

    public function before()
    {
        if (auth()->user()->isDeveloper() || auth()->user()->isRole('administrator')) {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function manage(User $user)
    {
        // are they a SGT and a division XO/CO?
        if ($user->isRole('senior leader') && \in_array($user->member->position, [5, 6], true)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function edit(User $user, MemberRequest $memberRequest)
    {
        if ($memberRequest->isApproved() || !$memberRequest->isCancelled()) {
            return false;
        }

        if ($user->isRole(['senior leader', 'administrator'])) {
            return true;
        }

        if ($memberRequest->requester_id === $user->member->clan_id) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function cancel(User $user, MemberRequest $request)
    {
        if ($user->isRole(['senior leader', 'administrator'])) {
            return true;
        }

        if ($request->requester_id === $user->member->clan_id) {
            return true;
        }

        return false;
    }

    public function view()
    {
        return true;
    }

    public function create()
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function update()
    {
    }

    /**
     * @return mixed
     */
    public function delete()
    {
    }
}
