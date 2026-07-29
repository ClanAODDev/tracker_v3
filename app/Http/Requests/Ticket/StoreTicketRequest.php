<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'description'    => 'required|string|min:25',
            'attachments'    => 'nullable|array|max:5',
            'attachments.*'  => 'file|image|max:1024',
        ];
    }
}
