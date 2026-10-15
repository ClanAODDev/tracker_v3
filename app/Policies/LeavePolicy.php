<?php

namespace App\Policies;

use App\Enums\Ability;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeavePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can(Ability::ViewLeaves);
    }

    public function create(User $user)
    {
        return $user->can(Ability::CreateLeaves);
    }

    public function update(User $user)
    {
        return $user->can(Ability::EditLeaves);
    }

    public function deleteAny(User $user)
    {
        return $user->can(Ability::EditLeaves);
    }
}
