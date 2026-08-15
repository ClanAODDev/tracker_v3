<?php

namespace App\Policies;

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransferPolicy
{
    public function before(User $user)
    {
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->isRole(['officer', 'sr_ldr']);
    }

    public function create(User $user): Response
    {
        if (! $user->division->active) {
            return Response::deny('Your division is not active.');
        }

        $cooldownDays   = config('aod.transfer.cooldown_days');
        $recentTransfer = Transfer::where('member_id', $user->member_id)
            ->where('created_at', '>=', now()->subDays($cooldownDays))
            ->first();

        if ($recentTransfer) {
            $nextAllowed = $recentTransfer->created_at->addDays($cooldownDays)->format('M j, Y');

            return Response::deny("Transfer requests can only be made once every {$cooldownDays} days. You can request again after {$nextAllowed}.");
        }

        return Response::allow();
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
