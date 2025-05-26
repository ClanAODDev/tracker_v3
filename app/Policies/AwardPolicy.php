<?php

namespace App\Policies;

use App\Models\User;

class AwardPolicy
{
    public function delete(User $user): bool
    {
        return $user->isDeveloper();
    }

    public static function deleteAny(User $user): bool
    {
        return $user->isDeveloper();
    }
}
