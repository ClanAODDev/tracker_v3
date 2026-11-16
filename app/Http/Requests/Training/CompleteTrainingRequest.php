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
            'module' => ['required', 'string'],
            'member' => ['required', 'string', 'exists:members,guid'],
        ];
    }

    public function messages(): array
    {
        return [
            'member.required' => 'Please select a member',
            'member.exists'   => 'That member appears to be invalid',
        ];
    }
}
