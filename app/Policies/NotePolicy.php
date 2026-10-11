<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Member;
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

    public function viewForMember(User $user, Member $member): bool
    {
        return $this->show($user) && ($this->outranksSubject($user, $member) || $this->hasAuthoredNoteAbout($user, $member));
    }

    public function createForMember(User $user, Member $member): bool
    {
        return $this->create($user) && $this->outranksSubject($user, $member);
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
        if ($this->restrictedByType($user, $note) || ! $this->outranksSubject($user, $note->member)) {
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
        if ($this->restrictedByType($user, $note) || ! $this->outranksSubject($user, $note->member)) {
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
        if ($this->restrictedByType($user, $note) || ! $this->outranksSubject($user, $note->member)) {
            return false;
        }

        return $user->isDivisionLeader();
    }

    public function forceDelete(User $user, Note $note): bool
    {
        if ($this->restrictedByType($user, $note) || ! $this->outranksSubject($user, $note->member)) {
            return false;
        }

        return $user->isDivisionLeader();
    }

    private function outranksSubject(User $user, Member $subject): bool
    {
        return $user->member?->outranks($subject) ?? false;
    }

    private function hasAuthoredNoteAbout(User $user, Member $subject): bool
    {
        return Note::withTrashed()->where('member_id', $subject->id)->where('author_id', $user->id)->exists();
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
