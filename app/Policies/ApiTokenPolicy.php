<?php

namespace App\Policies;

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
        return $user->member->rank_id > 6 && in_array($user->role_id, [2, 3, 4, 5]);
    }

    public function destroy(User $user, $token): bool
    {
        return auth()->user()->tokens->contains($token);
    }
}
