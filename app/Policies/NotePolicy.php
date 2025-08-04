<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct() {}

    public function before(User $user)
    {
        if ($user->isRole(['admin', 'sr_ldr']) || $user->isDeveloper()) {
            return true;
        }
    }

    /**
     * Officers and above can see notes.
     */
    public function show(User $user): bool
    {
        if ($user->isRole('member')) {
            return false;
        }

        return true;
    }

    /**
     * Only officers and above can create notes.
     */
    public function create(User $user): bool
    {
        if ($user->isRole('member')) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Note $note): bool
    {
        if ($user->isDivisionLeader() && $note->division_id === $user->division_id) {
            return true;
        }

        return false;
    }
}
