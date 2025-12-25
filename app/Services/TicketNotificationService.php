<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\Channel\NotifyAdminTicketCreated;
use App\Notifications\Channel\NotifyAdminTicketUpdated;
use App\Notifications\Channel\NotifyCallerTicketUpdated;
use App\Notifications\DM\NotifyNewTicketOwner;
use App\Notifications\DM\NotifyUserTicketCreated;
use App\Notifications\React\TicketReaction;

class TicketNotificationService
{
    public function notifyTicketCreated(Ticket $ticket): void
    {
        $ticket->notify(new NotifyUserTicketCreated);
        $ticket->notify(new NotifyAdminTicketCreated);

        if ($ticket->type->auto_assign_to) {
            $ticket->ownTo($ticket->type->auto_assign_to);
            $this->notifyTicketAssigned($ticket, $ticket->type->auto_assign_to, auth()->user());
        }
    }

    public function notifyTicketAssigned(Ticket $ticket, User $assignee, ?User $assigner = null): void
    {
        $assigner = $assigner ?? auth()->user();

        $ticket->notify(new NotifyCallerTicketUpdated($ticket, "Ticket has been assigned to {$assignee->name}"));
        $ticket->notify(new NotifyNewTicketOwner($assignee, $assigner));
        $ticket->notify(new TicketReaction('assigned'));
    }

    public function notifyTicketResolved(Ticket $ticket): void
    {
        $ticket->notify(new TicketReaction('resolved'));
        $ticket->caller->notify(new NotifyCallerTicketUpdated($ticket, 'Your ticket has been resolved.'));
    }

    public function notifyTicketRejected(Ticket $ticket, string $reason): void
    {
        $ticket->notify(new TicketReaction('rejected'));
        $ticket->caller->notify(new NotifyCallerTicketUpdated($ticket, "Your ticket was rejected: {$reason}"));
    }

    public function notifyCommentAdded(Ticket $ticket, TicketComment $comment): void
    {
        $isAdminComment = $comment->user?->isRole('admin');

        if ($isAdminComment && $ticket->caller_id !== $comment->user_id) {
            if ($ticket->caller?->ticket_notifications) {
                $ticket->caller->notify(new NotifyCallerTicketUpdated($ticket, $comment->body));
            }
        } elseif (! $isAdminComment && $ticket->owner_id && $ticket->owner_id !== $comment->user_id) {
            if ($ticket->owner?->ticket_notifications) {
                $ticket->owner->notify(new NotifyAdminTicketUpdated($ticket, $comment->body));
            }
        }
    }
}
