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
        if ($user->isDeveloper() || $user->isRole('admin')) {
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
        return $user->isRole(['officer', 'sr_ldr']);
    }

    public function editDivisionStructure(User $user)
    {
        return $user->isRole('sr_ldr');
    }

    public function manageUnassigned(User $user)
    {
        return $user->isRole('sr_ldr');
    }

    public function manageSlack(User $user)
    {
        return $user->isRole('sr_ldr') && $user->member->position_id == 6;
    }

    public function train(User $user)
    {
        return $user->member->rank_id > 9 && in_array($user->role_id, [4, 5]);
    }
}
