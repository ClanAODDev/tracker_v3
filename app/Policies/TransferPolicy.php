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
        return $this->isGainingDivisionLeader($user, $transfer);
    }

    public function hold(User $user, Transfer $transfer): bool
    {
        if ($transfer->approved_at) {
            return false;
        }

        return $this->isGainingDivisionLeader($user, $transfer)
            || $this->isLosingDivisionLeader($user, $transfer);
    }

    public function delete(User $user, Transfer $transfer): bool
    {
        if ($transfer->approved_at) {
            return false;
        }

        return $this->isGainingDivisionLeader($user, $transfer)
            || $this->isLosingDivisionLeader($user, $transfer);
    }

    private function isGainingDivisionLeader(User $user, Transfer $transfer): bool
    {
        return $user->division->id === $transfer->division_id && $user->isDivisionLeader();
    }

    private function isLosingDivisionLeader(User $user, Transfer $transfer): bool
    {
        return $user->division->id === $transfer->member->division_id && $user->isDivisionLeader();
    }
}
