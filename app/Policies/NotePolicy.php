<?php

namespace App\Policies;

use App\Enums\Ability;
use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class NotePolicy
{
    use HandlesAuthorization;

    public function __construct() {}

    public function before(User $user, ?string $ability = null, mixed ...$arguments)
    {
        if (in_array($ability, ['edit', 'delete', 'forceDelete'], true)
            && ($arguments[0] ?? null) instanceof Note
            && $user->member_id !== null
            && $arguments[0]->member_id === $user->member_id) {
            return Response::deny('You cannot edit or delete notes on your own profile');
        }

        if ($user->can(Ability::ManageAllNotes) || $user->isDeveloper()) {
            return true;
        }
    }

    public function show(User $user): bool
    {
        if (! $user->can(Ability::ViewNotes)) {
            return false;
        }

        return true;
    }

    public function edit(User $user, Note $note): bool
    {
        if ($this->restrictedByType($user, $note)) {
            return false;
        }

        return $user->isDivisionLeader() || $user->can(Ability::EditAnyNote);
    }

    public function create(User $user): bool
    {
        if (! $user->can(Ability::CreateNotes)) {
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
