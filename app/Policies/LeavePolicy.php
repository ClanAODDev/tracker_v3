<?php

namespace App\Policies;

use App\Enums\Ability;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeavePolicy
{
    use HandlesAuthorization;

    public function viewAny()
    {
        return auth()->user()->can(Ability::ViewLeaves);
    }

    public function create()
    {
        return auth()->user()->can(Ability::CreateLeaves);
    }

    public function update()
    {
        return auth()->user()->can(Ability::EditLeaves);
    }

    public function deleteAny()
    {
        return auth()->user()->can(Ability::EditLeaves);
    }
}
