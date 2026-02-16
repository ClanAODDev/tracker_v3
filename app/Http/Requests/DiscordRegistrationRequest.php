<?php

namespace App\Http\Requests;

use App\Enums\Position;
use App\Enums\Rank;
use App\Jobs\CreateForumAccount;
use App\Models\Division;
use App\Models\Member;
use App\Notifications\Channel\NotifyDivisionPendingDiscordRegistration;
use App\Services\AODForumService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class DiscordRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isPendingRegistration();
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (str_starts_with(strtolower($value), 'aod_')) {
                        $fail('Username cannot begin with "AOD_".');

                        return;
                    }

                    $lower = strtolower($value);
                    foreach (Rank::cases() as $rank) {
                        if (str_starts_with($lower, strtolower($rank->getAbbreviation()))) {
                            $fail('Username cannot begin with a rank abbreviation.');

                            return;
                        }
                    }
                },
            ],
            'date_of_birth' => ['required', 'date', 'before:' . now()->subYears(13)->format('Y-m-d')],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'division_id'   => ['required', 'exists:divisions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex'       => 'Username may only contain letters, numbers, and underscores.',
            'date_of_birth.before' => 'You must be at least 13 years old to join.',
        ];
    }

    public function persist(): void
    {
        $user = auth()->user();

        $user->update([
            'name'           => $this->validated('username'),
            'date_of_birth'  => $this->validated('date_of_birth'),
            'forum_password' => $this->validated('password'),
        ]);

        $division = Division::findOrFail($this->validated('division_id'));

        $co = $division->members()
            ->where('position', Position::COMMANDING_OFFICER)
            ->first();

        if (! $co) {
            throw ValidationException::withMessages([
                'division_id' => 'This division does not have a commanding officer. Please select a different division or contact an administrator.',
            ]);
        }

        $forumService      = app(AODForumService::class);
        $existingForumUser = $forumService->getUserByEmail($user->email);

        if ($existingForumUser) {
            $clanId = (int) $existingForumUser->userid;

            if (Member::where('clan_id', $clanId)->exists()) {
                throw ValidationException::withMessages([
                    'division_id' => 'A member with this forum account already exists. Please contact an administrator.',
                ]);
            }

            $user->update(['forum_password' => null]);
        } else {
            $password = $this->validated('password');
            $user->update(['forum_password' => null]);

            CreateForumAccount::dispatch(
                user: $user,
                impersonatingMemberId: $co->clan_id,
                username: $this->validated('username'),
                email: $user->email,
                dateOfBirth: $this->validated('date_of_birth'),
                password: $password,
                discordId: $user->discord_id,
            );
        }

        session(['pending_division_id' => $division->id]);

        if (! $division->settings()->get('application_required', false)) {
            $division->notify(new NotifyDivisionPendingDiscordRegistration($user));
        }
    }
}
