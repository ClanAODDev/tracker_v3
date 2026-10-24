<?php

namespace App\Policies;

use App\Enums\Ability;
use App\Models\User;

class MemberAwardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user): bool
    {
        return $user->can(Ability::ManageMemberAwards);
    }

    public function delete(User $user): bool
    {
        return $user->can(Ability::ManageMemberAwards);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Ability::ManageMemberAwards);
    }
}
