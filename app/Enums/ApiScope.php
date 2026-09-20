<?php

namespace App\Enums;

use App\Models\User;

enum ApiScope: string
{
    case ClanRead             = 'clan:read';
    case DivisionRead         = 'division:read';
    case DivisionReadAdvanced = 'division:read-advanced';
    case DivisionWrite        = 'division:write';

    public function label(): string
    {
        return match ($this) {
            self::ClanRead             => 'Clan: read',
            self::DivisionRead         => 'Division: read',
            self::DivisionReadAdvanced => 'Division: read (advanced)',
            self::DivisionWrite        => 'Division: write',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ClanRead             => 'Discord population counts and stream events.',
            self::DivisionRead         => 'Division listing and detail, including leadership.',
            self::DivisionReadAdvanced => 'Adds member lists and Discord IDs to division detail.',
            self::DivisionWrite        => 'Update a division\'s officer/member channel settings.',
        };
    }

    /**
     * @return ApiScope[]
     */
    public static function availableFor(User $user): array
    {
        $scopes = [self::ClanRead, self::DivisionRead];

        if (in_array($user->role, [Role::SENIOR_LEADER, Role::ADMIN], true)) {
            array_push($scopes, self::DivisionReadAdvanced, self::DivisionWrite);
        }

        return $scopes;
    }
}
