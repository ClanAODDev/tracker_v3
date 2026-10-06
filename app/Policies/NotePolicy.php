<?php

namespace App\Policies;

use App\Enums\Ability;
use App\Models\Member;
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
        $subject = is_string($arguments[0] ?? null) ? ($arguments[1] ?? null) : ($arguments[0] ?? null);

        if ($this->concernsOwnProfile($user, $ability, $subject)) {
            return Response::deny('Notes on your own profile are not available to you');
        }

        if ($user->can(Ability::ManageAllNotes) || $user->isDeveloper()) {
            return true;
        }
    }

    public function viewForMember(User $user, Member $member): bool
    {
        return $this->show($user);
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

    private function concernsOwnProfile(User $user, ?string $ability, mixed $subject): bool
    {
        $memberId = match (true) {
            $subject instanceof Note && in_array($ability, ['edit', 'delete', 'forceDelete'], true) => $subject->member_id,
            $subject instanceof Member && $ability === 'viewForMember'                              => $subject->id,
            default                                                                                 => null,
        };

        return $memberId !== null && $memberId === $user->member_id;
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
