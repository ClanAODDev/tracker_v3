<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscordRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isPendingRegistration();
    }

    public function rules(): array
    {
        return [
            'date_of_birth' => ['required', 'date', 'before:'.now()->subYears(18)->format('Y-m-d')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_of_birth.before' => 'You must be at least 18 years old to join.',
        ];
    }

    public function persist(): void
    {
        auth()->user()->update([
            'date_of_birth' => $this->validated('date_of_birth'),
            'forum_password' => $this->validated('password'),
        ]);
    }
}
