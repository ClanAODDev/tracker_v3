<?php

namespace App\Enums;

use App\Traits\EnumOptions;
use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    use EnumOptions;

    case MEMBER = 'member';
    case OFFICER = 'officer';
    case JUNIOR_LEADER = 'jr_ldr';
    case SENIOR_LEADER = 'sr_ldr';
    case ADMIN = 'admin';
    case BANNED = 'banned';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MEMBER => 'Member',
            self::OFFICER => 'Officer',
            self::JUNIOR_LEADER => 'Junior Leader',
            self::SENIOR_LEADER => 'Senior Leader',
            self::ADMIN => 'Administrator',
            self::BANNED => 'Banned',
        };
    }

    public function canAccessPanel(string $panelId): bool
    {
        if ($this === self::BANNED) {
            return false;
        }

        return match ($panelId) {
            'admin' => $this === self::ADMIN,
            'mod' => in_array($this, [self::ADMIN, self::SENIOR_LEADER, self::OFFICER]),
            'profile' => in_array($this, [self::ADMIN, self::SENIOR_LEADER, self::OFFICER, self::MEMBER]),
            default => false,
        };
    }

    public static function fromId(int $id): self
    {
        return match ($id) {
            1 => self::MEMBER,
            2 => self::OFFICER,
            3 => self::JUNIOR_LEADER,
            4 => self::SENIOR_LEADER,
            5 => self::ADMIN,
            6 => self::BANNED,
            default => throw new \ValueError("Invalid role ID: $id"),
        };
    }
}
