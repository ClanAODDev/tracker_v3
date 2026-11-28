<?php

namespace App\Policies;

use App\Enums\Ability;
use App\Models\Division;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DivisionPolicy
{
    use HandlesAuthorization;

    /**
     * @return bool
     */
    public function update(User $user, Division $division)
    {
        if ($user->can(Ability::ManageDivisions)) {
            return true;
        }

        return $user->member->isDivisionLeader($division);
    }

    public function delete(User $user, Division $division)
    {
        return $user->can(Ability::DeleteDivisions);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Ability::DeleteDivisions);
    }

    public function show(User $user)
    {
        if ($user->can(Ability::ManageDivisions)) {
            return true;
        }

        return false;
    }

    public function viewAny(User $user): bool
    {
        $division = $user->member->division;

        if ($user->can(Ability::ManageDivisions)) {
            return true;
        }

        if ($user->isDivisionLeader()) {
            return $user->member->division_id === $division->id;
        }

        return false;
    }

    public function view(User $user, Division $division)
    {
        if ($user->can(Ability::ManageDivisions)) {
            return true;
        }

        if ($user->member->division_id === $division->id) {
            return true;
        }

        return false;
    }

    public function create(User $user)
    {
        if ($user->can(Ability::ManageDivisions)) {
            return true;
        }

        return false;
    }
}
