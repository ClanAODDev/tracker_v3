<?php

namespace App\Authorization;

use App\Enums\Role;
use App\Models\User;

class ForumRoleSource implements RoleSource
{
    public function effectiveRole(User $user): ?Role
    {
        return $user->getEffectiveRole();
    }
}
