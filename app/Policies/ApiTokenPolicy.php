<?php

namespace App\Policies;

use App\Enums\Rank;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiTokenPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isDeveloper() || $user->isRole('admin')) {
            return true;
        }
    }

    public function create(User $user): bool
    {
        return $user->member->rank->value > Rank::TRAINER->value
            && \in_array($user->role, [Role::OFFICER, Role::SENIOR_LEADER, Role::ADMIN], true);
    }

    public function destroy(User $user, $token): bool
    {
        return auth()->user()->tokens->contains($token);
    }
}
