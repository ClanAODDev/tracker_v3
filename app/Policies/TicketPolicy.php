<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isRole('admin')) {
            return true;
        }
    }

    public function manage(User $user)
    {
        return false;
    }

    public function viewAny()
    {
        return true;
    }

    public function view(User $user, Ticket $ticket)
    {
        return $user->id === $ticket->caller_id;
    }

    public function create()
    {
        return true;
    }

    public function update()
    {
        return true;
    }

    public function delete()
    {
        return true;
    }

    public function restore()
    {
        return true;
    }

    public function forceDelete()
    {
        return true;
    }

    public function createComment()
    {
        return true;
    }

    public function deleteComment()
    {
        return true;
    }
}
