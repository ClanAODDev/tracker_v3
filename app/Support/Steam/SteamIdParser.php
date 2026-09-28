<?php

namespace App\Support\Steam;

use App\Enums\SteamInputFormat;

class SteamIdParser
{
    public const ACCOUNT_BASE = 76561197960265728;

    private const MAX_ACCOUNT_ID = 4294967295;

    private const INVITE_ALPHABET = 'bcdfghjkmnpqrtvw';

    public static function parse(string $input): ParsedSteamId
    {
        $value = trim($input);

        if ($value === '') {
            return self::invalid('Empty value');
        }

        if (str_contains($value, '...') || str_contains($value, '…')) {
            return self::invalid('Value was truncated');
        }

        if (preg_match('~steamcommunity\.com/profiles/([^/?#]+)~i', $value, $matches)) {
            return self::fromId64($matches[1], SteamInputFormat::PROFILE_URL);
        }

        if (preg_match('~steamcommunity\.com/id/([^/?#]+)~i', $value, $matches)) {
            return self::fromVanity($matches[1]);
        }

        if (preg_match('~s\.team/p/([^/?#]+)~i', $value, $matches)) {
            return self::fromInviteCode($matches[1]);
        }

        if (preg_match('~^(https?://|www\.)|\.[a-z]{2,}/~i', $value)) {
            return self::invalid('Not a Steam profile URL');
        }

        $bare = trim($value, "/= \t");

        if (preg_match('/^STEAM_[0-5]:([01]):(\d{1,10})$/i', $bare, $matches)) {
            return self::fromAccountId((int) $matches[2] * 2 + (int) $matches[1], SteamInputFormat::LEGACY);
        }

        if (preg_match('/^\[?U:1:(\d{1,10})]?$/i', $bare, $matches)) {
            return self::fromAccountId((int) $matches[1], SteamInputFormat::STEAM_ID3);
        }

        if (ctype_digit($bare)) {
            return self::fromNumber($bare);
        }

        return self::fromVanity($bare);
    }

    public static function isValidId64(string $value): bool
    {
        if (strlen($value) !== 17 || ! ctype_digit($value)) {
            return false;
        }

        $accountId = (int) $value - self::ACCOUNT_BASE;

        return $accountId >= 1 && $accountId <= self::MAX_ACCOUNT_ID;
    }

    private static function fromNumber(string $digits): ParsedSteamId
    {
        if (strlen($digits) === 17) {
            return self::fromId64($digits, SteamInputFormat::ID64);
        }

        if (str_starts_with($digits, '7656119')) {
            return self::invalid('SteamID64 is missing digits');
        }

        $accountId = ltrim($digits, '0');

        if ($accountId === '' || strlen($accountId) > 10) {
            return self::invalid('Number is not a SteamID64 or friend code');
        }

        return self::fromAccountId((int) $accountId, SteamInputFormat::FRIEND_CODE);
    }

    private static function fromId64(string $value, SteamInputFormat $format): ParsedSteamId
    {
        if (self::isValidId64($value)) {
            return new ParsedSteamId($format, steamId: $value);
        }

        if (ctype_digit($value) && strlen($value) < 17 && str_starts_with($value, '7656119')) {
            return self::invalid('SteamID64 is missing digits');
        }

        return self::invalid('Not a valid SteamID64');
    }

    private static function fromAccountId(int $accountId, SteamInputFormat $format): ParsedSteamId
    {
        if ($accountId < 1 || $accountId > self::MAX_ACCOUNT_ID) {
            return self::invalid('Number is not a SteamID64 or friend code');
        }

        return new ParsedSteamId($format, steamId: (string) (self::ACCOUNT_BASE + $accountId));
    }

    private static function fromInviteCode(string $code): ParsedSteamId
    {
        $code = strtolower(str_replace('-', '', $code));

        if (! preg_match('/^[' . self::INVITE_ALPHABET . ']{1,8}$/', $code)) {
            return self::invalid('Not a valid Steam invite link');
        }

        return self::fromAccountId(
            (int) hexdec(strtr($code, self::INVITE_ALPHABET, '0123456789abcdef')),
            SteamInputFormat::INVITE_LINK,
        );
    }

    private static function fromVanity(string $vanity): ParsedSteamId
    {
        if (! preg_match('/^[A-Za-z0-9_-]{2,32}$/', $vanity)) {
            return self::invalid('Not a SteamID or custom URL name');
        }

        return new ParsedSteamId(SteamInputFormat::VANITY, vanity: $vanity);
    }

    private static function invalid(string $reason): ParsedSteamId
    {
        return new ParsedSteamId(SteamInputFormat::INVALID, reason: $reason);
    }
}
