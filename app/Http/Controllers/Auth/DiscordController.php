<?php

namespace App\Http\Controllers\Auth;

use App\AOD\ClanForumPermissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DiscordRegistrationRequest;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\Member;
use App\Models\User;
use App\Notifications\Channel\NotifyDivisionPendingDiscordRegistration;
use App\Services\ForumProcedureService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class DiscordController extends Controller
{
    public function __construct(
        protected ClanForumPermissions $forumPermissions,
        protected ForumProcedureService $procedureService,
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
        $avatarHash      = $discordUser->getRaw()['avatar'] ?? null;

        if ($user = User::where('discord_id', $discordId)->first()) {
            return $this->loginExistingUser($user, $avatarHash);
        }

        if ($member = Member::where('discord_id', $discordId)->first()) {
            return $this->loginExistingMember(
                member: $member,
                discordId: $discordId,
                discordUsername: $discordUsername,
                email: $email,
                avatarHash: $avatarHash
            );
        }

        return $this->createPendingUser(
            discordId: $discordId,
            discordUsername: $discordUsername,
            email: $email,
            avatarHash: $avatarHash
        );
    }

    public function pending(): RedirectResponse|InertiaResponse
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

        if ($user->date_of_birth && ! $user->divisionApplication) {
            $divisionId = session('pending_division_id');
            $division   = $divisionId ? Division::find($divisionId) : null;
        }

        $errors        = session('errors');
        $hasFormErrors = $errors && $errors->getBag('default')
            ->hasAny(['username', 'date_of_birth', 'password', 'password_confirmation', 'division_id']);
        $needsRegistration = $user->date_of_birth === null || $hasFormErrors;

        return Inertia::render('auth/discord-pending', $this->buildPendingViewData($user, $division, $needsRegistration));
    }

    private function previewPending(User $user, string $divisionSlug): RedirectResponse|InertiaResponse
    {
        if (! $user->isRole(['admin', 'sr_ldr', 'officer'])) {
            return redirect('/');
        }

        $division = Division::where('slug', $divisionSlug)->firstOrFail();

        return Inertia::render('auth/discord-pending', $this->buildPendingViewData(
            $user,
            $division,
            needsRegistration: true,
            preview: true,
        ));
    }

    private function buildPendingViewData(User $user, ?Division $division, bool $needsRegistration, bool $preview = false): array
    {
        $applicationFields = collect();

        if ($division && $division->settings()->get('application_required', false)) {
            $applicationFields = $division->applicationFields->map(fn ($field) => [
                'id'         => $field->id,
                'label'      => strip_tags($field->label, '<strong><em><u><a>'),
                'helperText' => $field->helper_text ? strip_tags($field->helper_text, '<strong><em><u><a>') : null,
                'type'       => $field->type,
                'required'   => (bool) $field->required,
                'options'    => collect($field->options ?? [])->pluck('label')->all(),
            ]);
        }

        return [
            'discordUsername'   => $user->discord_username ?? $user->name,
            'email'             => $user->email,
            'defaultUsername'   => $user->name,
            'preview'           => $preview,
            'previewDivisionId' => $preview ? $division?->id : null,
            'needsRegistration' => $needsRegistration,
            'divisions'         => Division::active()->withoutFloaters()->withoutBR()->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'logo' => $d->getLogoPath()]),
            'applicationFields' => $applicationFields->values(),
        ];
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
            'user_id'        => $user->id,
            'division_id'    => $division->id,
            'discord_avatar' => session()->pull('discord_avatar_hash'),
            'responses'      => $responses,
        ]);

        session()->forget('pending_division_id');

        $division->notify(new NotifyDivisionPendingDiscordRegistration($user, application: $application));

        return redirect()->route('auth.discord.pending');
    }

    protected function loginExistingUser(User $user, ?string $avatarHash): RedirectResponse
    {
        auth()->login(user: $user, remember: true);
        request()->session()->regenerate();

        if ($avatarHash !== null && $avatarHash !== $user->discord_avatar) {
            $user->update(['discord_avatar' => $avatarHash]);
        }

        if ($user->member) {
            $this->forumPermissions->handleAccountRoles($user->member->clan_id);

            if ($user->discord_id) {
                $this->procedureService->setDiscordInfo(
                    userId: $user->member->clan_id,
                    discordId: $user->discord_id,
                    discordTag: $user->discord_username ?? '',
                );
            }

            DB::transaction(fn () => $this->syncMemberDiscordFields(
                $user->member,
                $user->discord_id,
                $user->discord_username,
                $avatarHash
            ));
        }

        return $user->isPendingRegistration()
            ? redirect()->route('auth.discord.pending')
            : redirect()->intended('/');
    }

    protected function loginExistingMember(
        Member $member,
        string $discordId,
        string $discordUsername,
        ?string $email,
        ?string $avatarHash = null
    ): RedirectResponse {
        $user = DB::transaction(function () use ($member, $email, $discordId, $discordUsername, $avatarHash) {
            $user = User::findOrCreateForMember($member, $email);

            $user->update([
                'discord_id'       => $discordId,
                'discord_username' => $discordUsername,
            ]);

            $this->syncMemberDiscordFields($member, $discordId, $discordUsername, $avatarHash);

            return $user;
        });

        auth()->login(user: $user, remember: true);
        request()->session()->regenerate();

        $this->forumPermissions->handleAccountRoles($member->clan_id);

        $this->procedureService->setDiscordInfo(
            userId: $member->clan_id,
            discordId: $discordId,
            discordTag: $discordUsername,
        );

        return redirect()->intended('/');
    }

    protected function syncMemberDiscordFields(
        Member $member,
        ?string $discordId,
        ?string $discordUsername,
        ?string $avatarHash
    ): void {
        $updates = [];

        if ($discordId) {
            $updates['discord_id'] = $discordId;
            $updates['discord']    = $discordUsername ?? $member->discord;
        }

        if ($avatarHash !== null) {
            $updates['discord_avatar'] = $avatarHash;
        }

        if ($updates) {
            $member->update($updates);
        }
    }

    protected function createPendingUser(
        string $discordId,
        string $discordUsername,
        ?string $email,
        ?string $avatarHash = null
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
        $uniqueName    = $this->makeUniqueName($sanitizedName);
        $hadCollision  = $uniqueName !== $sanitizedName;

        $user = User::create([
            'name'             => $uniqueName,
            'email'            => $email,
            'discord_id'       => $discordId,
            'discord_username' => $discordUsername,
            'discord_avatar'   => $avatarHash,
        ]);

        auth()->login(user: $user, remember: true);
        request()->session()->regenerate();

        if ($avatarHash !== null) {
            session(['discord_avatar_hash' => $avatarHash]);
        }

        $redirect = redirect()->route('auth.discord.pending');

        if ($hadCollision) {
            $redirect = $redirect->withErrors([
                'username' => 'Your chosen AOD username is already in use. Please choose a different one.',
            ]);
        }

        return $redirect;
    }

    protected function sanitizeName(string $name): string
    {
        $name = preg_replace(pattern: '/[^a-zA-Z0-9_]/', replacement: '', subject: $name);

        return substr($name, offset: 0, length: 50) ?: 'discord_user';
    }

    protected function makeUniqueName(string $base): string
    {
        $name = $base;
        $i    = 1;

        while (User::where('name', $name)->exists()) {
            $suffix = '_' . $i++;
            $name   = substr($base, 0, 50 - strlen($suffix)) . $suffix;
        }

        return $name;
    }
}
