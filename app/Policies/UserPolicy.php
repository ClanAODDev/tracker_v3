<?php

namespace App\Policies;

use App\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Class UserPolicy
 *
 * @package App\Policies
 */
class UserPolicy
{
    use AuthorizesRequests;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * @param User $user
     * @return bool
     */
    public function before(User $user)
    {
        if ($user->isDeveloper() || $user->isRole(['admin'])) {
            return true;
        }
    }

    /**
     * @param User $user
     * @param User $userOfMember
     * @return bool
     */
    public function update(User $user, User $userOfMember)
    {
        // can't update yourself
        if ($user->id === $userOfMember->id) {
            return false;
        }

        // cannot update a user of the same or higher role
        if ($user->role->id <= $userOfMember->role->id) {
            return false;
        }

        // senior leaders who are sgts and ssgts can update user accounts
        return $user->isRole('sr_ldr') && $user->member->isRank(['sgt', 'ssgt']);
    }

    public function canImpersonate($userBeingImpersonated)
    {
        return false;
    }
}
