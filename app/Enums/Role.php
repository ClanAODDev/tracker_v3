<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Role: int implements HasLabel
{
    case MEMBER = 1;
    case OFFICER = 2;
    case JUNIOR_LEADER = 3;
    case SENIOR_LEADER = 4;
    case ADMIN = 5;
    case BANNED = 6;

    public function slug(): string
    {
        return match ($this) {
            self::MEMBER => 'member',
            self::OFFICER => 'officer',
            self::JUNIOR_LEADER => 'jr_ldr',
            self::SENIOR_LEADER => 'sr_ldr',
            self::ADMIN => 'admin',
            self::BANNED => 'banned',
        };
    }

    public function getLabel(): string
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

    public static function fromSlug(string $slug): ?self
    {
        return match ($slug) {
            'member' => self::MEMBER,
            'officer' => self::OFFICER,
            'jr_ldr' => self::JUNIOR_LEADER,
            'sr_ldr' => self::SENIOR_LEADER,
            'admin' => self::ADMIN,
            'banned' => self::BANNED,
            default => null,
        };
    }
}
