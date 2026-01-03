<?php

namespace App\Policies;

use App\Models\Transfer;
use App\Models\User;

class TransferPolicy
{
    public function before(User $user)
    {
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }
    }

    public function create(User $user): bool
    {
        return $user->division->isActive();
    }

    public function approve(User $user, Transfer $transfer): bool
    {
        return $user->division->id === $transfer->division_id && $user->isDivisionLeader();
    }

    public function delete(User $user, Transfer $transfer): bool
    {
        if ($transfer->approved_at) {
            return false;
        }

        return $user->division->id === $transfer->division_id && $user->isDivisionLeader();
    }
}
