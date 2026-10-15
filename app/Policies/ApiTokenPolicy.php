<?php

namespace App\Policies;

use App\Enums\Ability;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiTokenPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isDeveloper() || $user->can(Ability::ManageApiTokens)) {
            return true;
        }
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function destroy(User $user): bool
    {
        return false;
    }
}
