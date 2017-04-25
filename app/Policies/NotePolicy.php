<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        // MSgts, SGTs, developers have access to all members
        if ($user->isRole(['admin', 'sr_ldr']) || $user->isDeveloper()) {
            return true;
        }
    }

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Only officers and above can create notes
     *
     * @return bool
     */
    public function create()
    {
        if (auth()->user()->isRole('member')) {
            return false;
        }

        return true;
    }

    /**
     * Officers and above can see notes
     * @return bool
     */
    public function show()
    {
        return $this->create();
    }
}
