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
     * @return bool
     */
    public function update(User $user)
    {
        if ($user->isRole(['admin'])) {
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
        return $user->isRole('officer');
    }

    public function editDivisionStructure(User $user)
    {
        return $user->isRole(['sr_ldr', 'admin']);
    }

    public function manageUnassigned(User $user)
    {
        return $user->isRole(['sr_ldr', 'admin']);
    }

    public function manageSlack(User $user)
    {
        return $user->isRole('sr_ldr') && $user->member->position_id == 6;
    }

    public function train(User $user)
    {
        return $user->canTrainMembers();
    }
}
