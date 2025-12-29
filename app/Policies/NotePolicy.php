<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
{
    use HandlesAuthorization;

    public function __construct() {}

    public function before(User $user)
    {
        if ($user->isRole('admin') || $user->isDeveloper()) {
            return true;
        }
    }

    public function show(User $user): bool
    {
        if ($user->isRole('member')) {
            return false;
        }

        return true;
    }

    public function edit(User $user, Note $note): bool
    {
        return $user->isDivisionLeader();
    }

    public function create(User $user): bool
    {
        if ($user->isRole('member')) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->isDivisionLeader();
    }

    public function viewTrashed(User $user): bool
    {
        return $user->isDivisionLeader();
    }

    public function restore(User $user, Note $note): bool
    {
        return $user->isDivisionLeader();
    }

    public function forceDelete(User $user, Note $note): bool
    {
        return $user->isDivisionLeader();
    }
}
