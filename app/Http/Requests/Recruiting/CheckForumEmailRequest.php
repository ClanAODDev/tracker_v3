<?php

namespace App\Http\Requests\Recruiting;

use Illuminate\Foundation\Http\FormRequest;

class CheckForumEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }
}
