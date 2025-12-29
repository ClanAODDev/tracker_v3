<?php

namespace App\Policies;

use App\Enums\Rank;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Class UserPolicy.
 */
class UserPolicy
{
    use AuthorizesRequests;

    public function before(User $user, string $ability)
    {
        if ($ability === 'impersonate') {
            return null;
        }

        if ($user->isDeveloper() || $user->isRole('admin')) {
            return true;
        }
    }

    /**
     * @param  User  $user
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

    public function impersonate(User $user, User $target)
    {
        if ($user->id === $target->id) {
            return false;
        }

        if (session('impersonating')) {
            return false;
        }

        if ($user->isDeveloper() && app()->environment('local', 'testing')) {
            return true;
        }

        if (! $user->isRole('admin')) {
            return false;
        }

        if ($target->isDeveloper()) {
            return false;
        }

        return true;
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

    public function train(User $user)
    {
        return $user->member->rank->value > Rank::SERGEANT->value && \in_array($user->role_id, [4, 5], true);
    }
}
