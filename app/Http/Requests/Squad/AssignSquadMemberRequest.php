<?php

namespace App\Http\Requests\Squad;

use Illuminate\Foundation\Http\FormRequest;

class AssignSquadMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|integer|exists:members,id',
            'squad_id'  => 'required|integer',
        ];
    }
}
