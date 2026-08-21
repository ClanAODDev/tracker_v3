<?php

namespace App\Policies;

use App\Enums\Rank;
use App\Models\RankAction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RankActionPolicy
{
    use HandlesAuthorization;

    public static function viewAny(User $user): bool
    {
        return $user->isRole(['officer', 'sr_ldr', 'admin']);
    }

    public static function update(User $user, RankAction $record): bool
    {
        $authedMemberId = $user->member_id;

        // can't see your own rank changes
        if ($record->member_id == $authedMemberId) {
            return false;
        }

        // admins can see all requests
        if ($user->isRole('admin')) {
            return true;
        }

        // can see requests you requested
        if ($record->requester_id == $authedMemberId) {
            return true;
        }

        // co/xo can see requests from their division below ssgt
        if (
            $user->isDivisionLeader() &&
            $record->member->division_id == $user->division->id &&
            $record->rank->isAtMost(Rank::STAFF_SERGEANT)
        ) {
            return true;
        }

        // platoon leader can see requests in their platoon below their rank
        if (
            $user->isPlatoonLeader() &&
            $record->member->platoon_id == $user->member->platoon_id &&
            $record->rank->isBelow($user->member->rank)
        ) {
            return true;
        }

        return false;
    }

    public static function deleteAny(): bool
    {
        return auth()->user()->isRole(['admin']);
    }

    public static function approve(User $user, RankAction $action): bool
    {
        $userRank = $user->member->rank;
        $newRank  = $action->rank;

        if ($newRank->value > $userRank->value) {
            return false;
        }

        if ($user->isWithinPlatoonLimit($newRank, $user->division)) {
            return true;
        }

        // For Sergeant and above, require that the user is at least Command Sergeant
        if ($newRank->value >= Rank::SERGEANT->value) {
            return $userRank->value >= Rank::COMMAND_SERGEANT->value;
        }

        return self::isAdminOrDivisionLeader($user);
    }

    public static function manageComments(User $user, RankAction $action): bool
    {
        $userRank = $user->member->rank;
        $newRank  = $action->rank;

        // For Sergeant and above, require that the user is at least Master Sergeant
        if ($newRank->value >= Rank::SERGEANT->value) {
            return $userRank->value >= Rank::MASTER_SERGEANT->value;
        }

        if ($user->isWithinPlatoonLimit($newRank, $user->division)) {
            return true;
        }

        return self::isAdminOrDivisionLeader($user);
    }

    private static function isAdminOrDivisionLeader(User $user): bool
    {
        return $user->isDivisionLeader() || $user->isRole('admin');
    }
}
