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
     * @param User $user
     * @return bool
     */
    public function before(User $user)
    {
        if ($user->isDeveloper()) {
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

        if ($user->isRole(['admin'])) {
            return true;
        }

        // cannot update a user of the same or higher role
        if ($user->role->id <= $userOfMember->role->id) {
            return false;
        }

        // senior leaders who are sgts and ssgts can update user accounts
        if ($user->isRole('sr_ldr') && $user->member->isRank(['sgt', 'ssgt'])) {
            return true;
        }

        // jr leaders can create officers
        if ($user->isRole('jr_ldr') && ($userOfMember->role_id < $user->role_id)) {
            return true;
        }

        return false;
    }

    public function viewAny(User $user)
    {
        if ($user->isRole(['admin'])) {
            return true;
        }

        return false;
    }

    public function view(User $user)
    {
        if ($user->isRole(['admin'])) {
            return true;
        }

        return false;
    }

    public function create(User $user)
    {
        if ($user->isRole(['admin'])) {
            return true;
        }

        return false;
    }

    public function delete(User $user)
    {
        if ($user->isRole(['admin'])) {
            return true;
        }

        return false;
    }

    public function restore(User $user)
    {
        if ($user->isRole(['admin'])) {
            return true;
        }

        return false;
    }

    public function forceDelete(User $user)
    {
        if ($user->isRole(['admin'])) {
            return true;
        }

        return false;
    }

    public function canImpersonate($userBeingImpersonated)
    {
        return false;
    }

    public function viewDivisionStructure(User $user)
    {
        return $user->isRole(['jr_ldr', 'sr_ldr', 'admin']);
    }

    public function manageUnassigned(User $user)
    {
        return $user->isRole(['jr_ldr', 'sr_ldr', 'admin']);
    }

    public function manageIssues(User $user)
    {
        return $user->isRole(['jr_ldr', 'sr_ldr']);
    }

    public function manageSlack(User $user)
    {
        return $user->isRole('sr_ldr') && $user->member->position_id == 6;
    }

    public function train(User $user)
    {
        return $user->canTrainMembers();
    }

    public function canAssignTickets(User $user)
    {
        return $user->isRole('admin');
    }
}
