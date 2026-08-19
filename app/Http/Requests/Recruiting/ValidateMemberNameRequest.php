<?php

namespace App\Http\Requests\Recruiting;

use Illuminate\Foundation\Http\FormRequest;

class ValidateMemberNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string',
            'member_id' => 'nullable|integer',
            'email'     => 'nullable|string',
        ];
    }
}
