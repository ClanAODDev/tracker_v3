<?php

namespace App\Http\Requests;

use App\Models\Division;
use App\Models\DivisionApplication;
use App\Notifications\Channel\NotifyDivisionPendingDiscordRegistration;
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
            'date_of_birth' => ['required', 'date', 'before:' . now()->subYears(13)->format('Y-m-d')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'division_id' => ['required', 'exists:divisions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_of_birth.before' => 'You must be at least 13 years old to join.',
        ];
    }

    public function persist(): void
    {
        $user = auth()->user();

        $user->update([
            'date_of_birth' => $this->validated('date_of_birth'),
            'forum_password' => $this->validated('password'),
        ]);

        $division = Division::findOrFail($this->validated('division_id'));

        DivisionApplication::create([
            'user_id' => $user->id,
            'division_id' => $division->id,
            'responses' => [],
        ]);

        $division->notify(new NotifyDivisionPendingDiscordRegistration($user));
    }
}
