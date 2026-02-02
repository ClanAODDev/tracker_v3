<?php

namespace App\Http\Requests;

use App\Models\Division;
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
            'division_id' => ['nullable', 'exists:divisions,id'],
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
        auth()->user()->update([
            'date_of_birth' => $this->validated('date_of_birth'),
            'forum_password' => $this->validated('password'),
            'division_id' => $this->validated('division_id'),
        ]);

        $this->notifyDivision();
    }

    private function notifyDivision(): void
    {
        $divisionId = $this->validated('division_id');

        if (! $divisionId) {
            return;
        }

        $division = Division::find($divisionId);

        if ($division) {
            $division->notify(new NotifyDivisionPendingDiscordRegistration(auth()->user()));
        }
    }
}
