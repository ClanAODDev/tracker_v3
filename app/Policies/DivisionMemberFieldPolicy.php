<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\DivisionMemberField;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DivisionMemberFieldPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }
    }

    public function viewAny(User $user, ?Division $division = null): bool
    {
        if ($division) {
            return $this->isLeaderOf($user, $division);
        }

        return $user->isDivisionLeader();
    }

    public function create(User $user, ?Division $division = null): bool
    {
        if ($division) {
            return $this->isLeaderOf($user, $division);
        }

        return $user->isDivisionLeader();
    }

    public function update(User $user, DivisionMemberField $field): bool
    {
        return $this->isLeaderOf($user, $field->division);
    }

    public function delete(User $user, DivisionMemberField $field): bool
    {
        return $this->isLeaderOf($user, $field->division);
    }

    private function isLeaderOf(User $user, Division $division): bool
    {
        return $user->member?->isDivisionLeader($division) ?? false;
    }
}
