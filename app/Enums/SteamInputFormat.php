<?php

namespace App\Enums;

enum SteamInputFormat: string
{
    case ID64        = 'id64';
    case PROFILE_URL = 'profile_url';
    case LEGACY      = 'legacy';
    case STEAM_ID3   = 'steam_id3';
    case FRIEND_CODE = 'friend_code';
    case INVITE_LINK = 'invite_link';
    case VANITY      = 'vanity';
    case INVALID     = 'invalid';

    public function label(): string
    {
        return match ($this) {
            self::ID64        => 'SteamID64',
            self::PROFILE_URL => 'Profile URL',
            self::LEGACY      => 'Legacy STEAM_X:Y:Z',
            self::STEAM_ID3   => 'SteamID3',
            self::FRIEND_CODE => 'Friend code',
            self::INVITE_LINK => 'Invite link',
            self::VANITY      => 'Custom URL name',
            self::INVALID     => 'Invalid',
        };
    }

    public function isGuessedFromNumber(): bool
    {
        return in_array($this, [self::FRIEND_CODE, self::INVITE_LINK], true);
    }
}
