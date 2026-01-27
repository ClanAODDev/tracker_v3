<?php

namespace App\Http\Controllers\Auth;

use App\AOD\ClanForumPermissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\DiscordRegistrationRequest;
use App\Models\Division;
use App\Models\Member;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

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
        } catch (ClientException) {
            return redirect()->route('login')->withErrors([
                'discord' => 'Discord authentication failed. Please try again.',
            ]);
        }

        $discordId = $discordUser->getId();
        $discordUsername = $discordUser->getNickname() ?? $discordUser->getName();
        $email = $discordUser->getEmail();

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
        if (! auth()->user()->isPendingRegistration()) {
            return redirect('/');
        }

        $divisions = Division::active()
            ->withoutFloaters()
            ->orderBy('name')
            ->get(['id', 'name', 'abbreviation']);

        return view('auth.discord-pending', compact('divisions'));
    }

    public function register(DiscordRegistrationRequest $request): RedirectResponse
    {
        $request->persist();

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
            'discord_id' => $discordId,
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

        $user = User::create([
            'name' => $this->sanitizeName($discordUsername),
            'email' => $email,
            'discord_id' => $discordId,
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
