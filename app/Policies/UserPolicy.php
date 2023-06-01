<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Class UserPolicy.
 */
class UserPolicy
{
    use AuthorizesRequests;

    /**
     * @return bool
     */
    public function before(User $user)
    {
        if ($user->isDeveloper() || $user->isRole('administrator')) {
            return true;
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function update()
    {
        return false;
    }

    public function viewAny()
    {
        return false;
    }

    public function view()
    {
        return false;
    }

    public function create()
    {
        return false;
    }

    public function delete()
    {
        return false;
    }

    public function restore()
    {
        return false;
    }

    public function forceDelete()
    {
        return false;
    }

    public function canImpersonate()
    {
        return false;
    }

    public function viewDivisionStructure(User $user)
    {
        return $user->isRole(['officer', 'senior leader']);
    }

    public function editDivisionStructure(User $user)
    {
        return $user->isRole('senior leader');
    }

    public function manageUnassigned(User $user)
    {
        return $user->isRole('senior leader');
    }

    public function manageSlack(User $user)
    {
        return $user->isRole('senior leader') && 6 === $user->member->position;
    }

    public function train(User $user)
    {
        return $user->member->rank_id > 9
            && \in_array($user->role, [
                Role::SENIOR_LEADER, Role::ADMINISTRATOR,
            ], true);
    }
}
