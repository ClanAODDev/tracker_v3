<?php

namespace App\Policies;

use App\Enums\Role;
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
        if ($this->restrictedByType($user, $note)) {
            return false;
        }

        return $user->isDivisionLeader() || $user->isRole(Role::SENIOR_LEADER);
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
        if ($this->restrictedByType($user, $note)) {
            return false;
        }

        return $user->isDivisionLeader();
    }

    public function viewTrashed(User $user): bool
    {
        return $user->isDivisionLeader();
    }

    public function restore(User $user, Note $note): bool
    {
        if ($this->restrictedByType($user, $note)) {
            return false;
        }

        return $user->isDivisionLeader();
    }

    public function forceDelete(User $user, Note $note): bool
    {
        if ($this->restrictedByType($user, $note)) {
            return false;
        }

        return $user->isDivisionLeader();
    }

    private function restrictedByType(User $user, Note $note): bool
    {
        return match ($note->type) {
            'msgt'   => ! Note::canManageMsgt($user),
            'sr_ldr' => ! Note::canManageSrLdr($user),
            default  => false,
        };
    }
}
