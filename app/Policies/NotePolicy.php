<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }

    public function before(User $user)
    {
        // MSgts, SGTs, developers have access to all members
        if ($user->isRole(['administratosr', 'senior leader']) || $user->isDeveloper()) {
            return true;
        }
    }

    /**
     * Officers and above can see notes.
     *
     * @return bool
     */
    public function show()
    {
        return $this->create();
    }

    public function edit()
    {
    }

    /**
     * Only officers and above can create notes.
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

    public function delete()
    {
        if (auth()->user()->isRole('member')) {
            return false;
        }

        return true;
    }
}
