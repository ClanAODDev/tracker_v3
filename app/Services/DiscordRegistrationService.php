<?php

namespace App\Services;

use App\Models\Member;
use App\Models\User;

class DiscordRegistrationService
{
    /**
     * Create a brand-new pending-registration user for a first-time Discord
     * login, de-duplicating the AOD username derived from their Discord name.
     *
     * @return array{0: User, 1: bool} the created user, and whether its name collided with an existing one
     */
    public function createPendingUser(
        string $discordId,
        string $discordUsername,
        string $email,
        ?string $avatarHash,
    ): array {
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

        return [$user, $hadCollision];
    }

    public function sanitizeName(string $name): string
    {
        $name = preg_replace(pattern: '/[^a-zA-Z0-9_]/', replacement: '', subject: $name);

        return substr($name, offset: 0, length: 50) ?: 'discord_user';
    }

    public function makeUniqueName(string $base): string
    {
        $name = $base;
        $i    = 1;

        while (User::where('name', $name)->exists()) {
            $suffix = '_' . $i++;
            $name   = substr($base, 0, 50 - strlen($suffix)) . $suffix;
        }

        return $name;
    }

    public function syncMemberDiscordFields(
        Member $member,
        ?string $discordId,
        ?string $discordUsername,
        ?string $avatarHash,
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
}
