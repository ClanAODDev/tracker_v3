<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use App\Notifications\NotifyAdminTicketCreated;
use App\Notifications\NotifyCallerTicketUpdated;
use App\Notifications\NotifyNewTicketOwner;
use App\Notifications\NotifyUserTicketCreated;
use App\Notifications\TicketReaction;
use Illuminate\Foundation\Http\FormRequest;

class CreateTicket extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'ticket_type' => 'required',
            'description' => 'string|min:25|required',
        ];
    }

    public function persist()
    {
        $validated = $this->safe();

        $ticket = Ticket::create([
            'state' => 'new',
            'ticket_type_id' => $validated['ticket_type'],
            'description' => $validated['description'],
            'caller_id' => auth()->id(),
            'division_id' => auth()->user()->member->division_id,
        ]);

        $ticket->notify(new NotifyUserTicketCreated);
        $ticket->notify(new NotifyAdminTicketCreated);

        if ($ticket->type->auto_assign_to) {
            $ticket->ownTo($ticket->type->auto_assign_to);
            $ticket->notify(new NotifyCallerTicketUpdated('Ticket has been assigned to ' . $ticket->type->auto_assign_to->name));
            $ticket->notify(new NotifyNewTicketOwner($ticket->type->auto_assign_to, auth()->user()));
            $ticket->notify(new TicketReaction('assigned'));
        }

        return $ticket;
    }
}
