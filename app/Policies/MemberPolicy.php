<?php

namespace App\Policies;

use App\User;
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
    public function before(User $user)
    {
        return $user->isRole('admin') || $user->isDeveloper();
    }

    public function store(Member $member)
    {
        return true;
    }
}
