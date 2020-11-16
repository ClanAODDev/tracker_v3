<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use \Illuminate\Http\Request;

class TicketCommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('createComment', $ticket);

        $validated = $request->validate([
            'comment' => 'string|min:5|required'
        ]);

        $ticket->comments()->create([
            'body' => $validated['comment'],
            'user_id' => auth()->id(),
        ]);

        return redirect(route('help.tickets.show', $ticket));
    }

    public function delete(Ticket $ticket, TicketComment $comment)
    {
        $this->authorize('deleteComment', $comment);

        $comment->delete();

        $this->showToast('Comment successfully deleted');

        return redirect(route('help.tickets.show', $ticket));
    }
}
