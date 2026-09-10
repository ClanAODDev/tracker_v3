<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class CompleteTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('train', $user);
    }

    public function rules(): array
    {
        return [
            'module'  => ['required', 'string'],
            'clan_id' => ['required', 'exists:members,clan_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'clan_id.required' => 'Please select a member',
            'clan_id.exists'   => 'That member appears to be invalid',
        ];
    }
}
