<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class MemberPolicy
{
    use HandlesAuthorization;

    /**
     * Admins and developers have access to all members
     *
     * @param $user
     * @return bool
     */
    public function before($user)
    {
        return $user->isAdmin() || $user->isDeveloper();
    }

    public function store(Member $member)
    {
        return true;
    }
}
