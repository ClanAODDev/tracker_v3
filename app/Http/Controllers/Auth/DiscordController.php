<?php

namespace App\Http\Controllers\Auth;

use App\AOD\ClanForumPermissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\DiscordRegistrationRequest;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\Member;
use App\Models\User;
use App\Notifications\Channel\NotifyDivisionPendingDiscordRegistration;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class DiscordController extends Controller
{
    public function __construct(
        protected ClanForumPermissions $forumPermissions
    ) {}

    public function redirect()
    {
        return Socialite::driver('discord')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $discordUser = Socialite::driver('discord')->user();
        } catch (ClientException|InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'discord' => 'Discord authentication failed. Please try again.',
            ]);
        }

        $discordId       = $discordUser->getId();
        $discordUsername = $discordUser->getNickname() ?? $discordUser->getName();
        $email           = $discordUser->getEmail();

        if ($user = User::where('discord_id', $discordId)->first()) {
            return $this->loginExistingUser($user);
        }

        if ($member = Member::where('discord_id', $discordId)->first()) {
            return $this->loginExistingMember(
                member: $member,
                discordId: $discordId,
                discordUsername: $discordUsername,
                email: $email
            );
        }

        return $this->createPendingUser(
            discordId: $discordId,
            discordUsername: $discordUsername,
            email: $email
        );
    }

    public function pending(): RedirectResponse|View
    {
        $user        = auth()->user();
        $previewSlug = request('preview');

        if ($previewSlug) {
            return $this->previewPending($user, $previewSlug);
        }

        if (! $user->isPendingRegistration()) {
            return redirect('/');
        }

        $division = null;

        if ($user->date_of_birth && ! $user->forum_password && ! $user->divisionApplication) {
            $divisionId = session('pending_division_id');
            $division   = $divisionId ? Division::find($divisionId) : null;
        }

        return view('auth.discord-pending', $this->buildPendingViewData($division));
    }

    private function previewPending(User $user, string $divisionSlug): RedirectResponse|View
    {
        if (! $user->isRole(['admin', 'sr_ldr', 'officer'])) {
            return redirect('/');
        }

        $division = Division::where('slug', $divisionSlug)->firstOrFail();

        return view('auth.discord-pending', array_merge(
            $this->buildPendingViewData($division),
            ['preview' => true, 'previewDivision' => $division]
        ));
    }

    private function buildPendingViewData(?Division $division): array
    {
        $divisions = Division::active()
            ->withoutFloaters()
            ->withoutBR()
            ->orderBy('name')
            ->get(['id', 'name', 'abbreviation']);

        $applicationFields = null;
        $needsApplication  = false;

        if ($division && $division->settings()->get('application_required', false)) {
            $applicationFields = $division->applicationFields;
            $needsApplication  = $applicationFields->isNotEmpty();
        }

        return compact('divisions', 'applicationFields', 'needsApplication');
    }

    public function register(DiscordRegistrationRequest $request): RedirectResponse
    {
        $request->persist();

        return redirect()->route('auth.discord.pending');
    }

    public function submitApplication(Request $request): RedirectResponse
    {
        $user       = auth()->user();
        $divisionId = session('pending_division_id');

        if (! $user->isPendingRegistration() || ! $divisionId) {
            return redirect()->route('auth.discord.pending');
        }

        $division = Division::findOrFail($divisionId);
        $fields   = $division->applicationFields;

        $rules = [];
        foreach ($fields as $field) {
            $key            = "field_{$field->id}";
            $allowedOptions = collect($field->options ?? [])->pluck('label')->all();

            $rules[$key] = match ($field->type) {
                'checkbox' => array_filter([
                    $field->required ? 'required' : 'nullable',
                    'array',
                    $field->required ? 'min:1' : null,
                ]),
                'radio' => array_filter([
                    $field->required ? 'required' : 'nullable',
                    'string',
                    $allowedOptions ? 'in:' . implode(',', $allowedOptions) : null,
                ]),
                default => [
                    $field->required ? 'required' : 'nullable',
                    'string',
                    'max:500',
                ],
            };

            if ($field->type === 'checkbox' && $allowedOptions) {
                $rules["{$key}.*"] = ['string', 'in:' . implode(',', $allowedOptions)];
            }
        }

        $validated = $request->validate($rules);

        $responses = [];
        foreach ($fields as $field) {
            $key                   = "field_{$field->id}";
            $responses[$field->id] = [
                'label' => $field->label,
                'value' => $validated[$key] ?? null,
            ];
        }

        $application = DivisionApplication::create([
            'user_id'     => $user->id,
            'division_id' => $division->id,
            'responses'   => $responses,
        ]);

        session()->forget('pending_division_id');

        $division->notify(new NotifyDivisionPendingDiscordRegistration($user, application: $application));

        return redirect()->route('auth.discord.pending');
    }

    protected function loginExistingUser(User $user): RedirectResponse
    {
        Auth::login(user: $user, remember: true);

        if ($user->member) {
            $this->forumPermissions->handleAccountRoles($user->member->clan_id);
        }

        return $user->isPendingRegistration()
            ? redirect()->route('auth.discord.pending')
            : redirect()->intended('/');
    }

    protected function loginExistingMember(
        Member $member,
        string $discordId,
        string $discordUsername,
        ?string $email
    ): RedirectResponse {
        $user = User::findOrCreateForMember($member, $email);
        $user->update([
            'discord_id'       => $discordId,
            'discord_username' => $discordUsername,
        ]);

        Auth::login(user: $user, remember: true);

        $this->forumPermissions->handleAccountRoles($member->clan_id);

        return redirect()->intended('/');
    }

    protected function createPendingUser(
        string $discordId,
        string $discordUsername,
        ?string $email
    ): RedirectResponse {
        if (! $email) {
            return redirect()->route('login')->withErrors([
                'discord' => 'Your Discord account was created with a phone number. Please add an email to your Discord account to sign in.',
            ]);
        }

        if (User::where('email', $email)->exists()) {
            return redirect()->route('login')->withErrors([
                'discord' => 'An account with this email already exists. Please sign in with your forum credentials.',
            ]);
        }

        $sanitizedName = $this->sanitizeName($discordUsername);

        if (User::where('name', $sanitizedName)->exists()) {
            return redirect()->route('login')->withErrors([
                'discord' => 'An account with this username already exists. Please sign in with your forum credentials or contact an administrator.',
            ]);
        }

        $user = User::create([
            'name'             => $sanitizedName,
            'email'            => $email,
            'discord_id'       => $discordId,
            'discord_username' => $discordUsername,
        ]);

        Auth::login(user: $user, remember: true);

        return redirect()->route('auth.discord.pending');
    }

    protected function sanitizeName(string $name): string
    {
        $name = preg_replace(pattern: '/[^a-zA-Z0-9_]/', replacement: '', subject: $name);

        return substr($name, offset: 0, length: 50) ?: 'discord_user';
    }
}
