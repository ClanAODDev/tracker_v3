<?php

namespace App\Http\Requests\Recruiting;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;

class ValidateMemberNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('recruit', Member::class) ?? false;
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
