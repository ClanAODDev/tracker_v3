<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use App\Notifications\NotifyAdminTicketCreated;
use App\Notifications\NotifyUserTicketCreated;
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
        $ticket = Ticket::create([
            'state' => 'new',
            'ticket_type_id' => $this->validated['ticket_type'],
            'description' => $this->validated['description'],
            'caller_id' => auth()->id(),
            'division_id' => auth()->user()->member->division_id,
        ]);

        // send a message to admin channel as well as to the caller
        $ticket->notify(new NotifyUserTicketCreated());
        $ticket->notify(new NotifyAdminTicketCreated());
    }
}
