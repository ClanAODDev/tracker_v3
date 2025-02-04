<?php

namespace App\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Parallax\FilamentComments\Models\FilamentComment;

class RankActionCommentsPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, FilamentComment $filamentComment): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, FilamentComment $filamentComment): bool
    {
        return false;
    }

    public function delete(Authenticatable $user, FilamentComment $filamentComment): bool
    {
        return $user->id === $filamentComment->user_id;
    }

    public function deleteAny(Authenticatable $user): bool
    {
        return true;
    }

    public function restore(Authenticatable $user, FilamentComment $filamentComment): bool
    {
        return false;
    }

    public function restoreAny(Authenticatable $user): bool
    {
        return false;
    }

    public function forceDelete(Authenticatable $user, FilamentComment $filamentComment): bool
    {
        return false;
    }

    public function forceDeleteAny(Authenticatable $user): bool
    {
        return false;
    }
}
