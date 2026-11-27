<?php

namespace App\Authorization;

use App\Enums\Role;
use App\Models\User;

interface RoleSource
{
    public function effectiveRole(User $user): ?Role;
}
