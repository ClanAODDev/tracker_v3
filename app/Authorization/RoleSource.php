<?php

namespace App\Authorization;

use App\Enums\Role;
use App\Models\User;

interface RoleSource
{
    public function storedRole(User $user): ?Role;

    public function effectiveRole(User $user): ?Role;
}
