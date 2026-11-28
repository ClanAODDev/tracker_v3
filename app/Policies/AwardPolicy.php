<?php

namespace App\Policies;

use App\Enums\Ability;
use App\Models\User;

class AwardPolicy
{
    public function delete(User $user): bool
    {
        return $user->can(Ability::DeleteAwards);
    }

    public static function deleteAny(User $user): bool
    {
        return $user->can(Ability::DeleteAwards);
    }
}
