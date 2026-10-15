<?php

namespace App\Authorization;

use App\Enums\Ability;
use App\Models\User;

interface AbilityMap
{
    public function allows(User $user, Ability $ability): bool;
}
