<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Role: int implements HasColor, HasLabel
{
    case MEMBER        = 1;
    case OFFICER       = 2;
    case SENIOR_LEADER = 3;
    case ADMIN         = 4;
    case BANNED        = 5;

    public function slug(): string
    {
        return match ($this) {
            self::MEMBER        => 'member',
            self::OFFICER       => 'officer',
            self::SENIOR_LEADER => 'sr_ldr',
            self::ADMIN         => 'admin',
            self::BANNED        => 'banned',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::MEMBER        => 'Member',
            self::OFFICER       => 'Officer',
            self::SENIOR_LEADER => 'Senior Leader',
            self::ADMIN         => 'Administrator',
            self::BANNED        => 'Banned',
        };
    }

    public static function fromSlug(string $slug): ?self
    {
        return match ($slug) {
            'member'  => self::MEMBER,
            'officer' => self::OFFICER,
            'sr_ldr'  => self::SENIOR_LEADER,
            'admin'   => self::ADMIN,
            'banned'  => self::BANNED,
            default   => null,
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MEMBER        => 'gray',
            self::OFFICER       => 'info',
            self::SENIOR_LEADER => 'success',
            self::ADMIN         => 'danger',
            self::BANNED        => 'gray',
        };
    }
}
