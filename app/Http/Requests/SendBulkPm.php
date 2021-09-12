<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\ValidationRules\Rules\Delimited;

class SendBulkPm extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Member::class);
    }

    public function rules(): array
    {
        return [
            'pm-member-data' => [(new Delimited('numeric', $this->messages()))->min(2)],
        ];
    }

    public function messages(): array
    {
        return [
            'pm-member-data.min' => 'You must select at least :min members',
        ];
    }
}
