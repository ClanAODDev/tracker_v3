<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class LeavePolicy
{
    use HandlesAuthorization;

    public function create()
    {
        return ! auth()->user()->isRole('member');
    }

    public function update()
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public function deleteAny()
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }
}
