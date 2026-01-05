<?php

namespace App\Http\Controllers\Auth;

use App\AOD\ClanForumPermissions;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('discord')->redirect();
    }

    public function callback()
    {
        $discordUser = Socialite::driver('discord')->user();
        $discordId = $discordUser->getId();
        $discordUsername = $discordUser->getNickname() ?? $discordUser->getName();
        $email = $discordUser->getEmail();

        $user = User::where('discord_id', $discordId)->first();

        if ($user) {
            Auth::login($user, true);

            if ($user->member) {
                app(ClanForumPermissions::class)->handleAccountRoles($user->member->clan_id);
            }

            return $user->isPendingRegistration()
                ? redirect()->route('auth.discord.pending')
                : redirect()->intended('/');
        }

        $member = Member::where('discord_id', $discordId)->first();

        if ($member) {
            $user = User::findOrCreateForMember($member, $email);
            $user->update([
                'discord_id' => $discordId,
                'discord_username' => $discordUsername,
            ]);

            Auth::login($user, true);

            app(ClanForumPermissions::class)->handleAccountRoles($member->clan_id);

            return redirect()->intended('/');
        }

        return redirect()->route('login')->withErrors([
            'discord' => 'Your Discord account is not linked to an AOD member. Please ensure you have completed the auth link process, and try again..',
        ]);

        // @todo Re-enable new account creation when ready for recruitment flow
        // if (! $email) {
        //     return redirect()->route('login')->withErrors([
        //         'discord' => 'Your Discord account was created with a phone number. Please add an email to your Discord account to sign in.',
        //     ]);
        // }
        //
        // if (User::where('email', $email)->exists()) {
        //     return redirect()->route('login')->withErrors([
        //         'discord' => 'An account with this email already exists. Please sign in with your forum credentials.',
        //     ]);
        // }
        //
        // $user = User::create([
        //     'name' => $this->sanitizeName($discordUsername),
        //     'email' => $email,
        //     'discord_id' => $discordId,
        //     'discord_username' => $discordUsername,
        // ]);
        //
        // Auth::login($user, true);
        //
        // return redirect()->route('auth.discord.pending');
    }

    public function pending()
    {
        if (! auth()->user()->isPendingRegistration()) {
            return redirect('/');
        }

        return view('auth.discord-pending');
    }

    protected function sanitizeName(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);

        return substr($name, 0, 50) ?: 'discord_user';
    }
}
