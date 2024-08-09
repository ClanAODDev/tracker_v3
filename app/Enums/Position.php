<?php

namespace App\Enums;

use App\Traits\EnumOptions;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Position: int implements HasIcon, HasLabel
{
    use EnumOptions;

    case MEMBER = 1;
    case SQUAD_LEADER = 2;
    case PLATOON_LEADER = 3;
    case GENERAL_SERGEANT = 4;
    case EXECUTIVE_OFFICER = 5;
    case COMMANDING_OFFICER = 6;
    case CLAN_ADMIN = 7;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MEMBER => 'Member',
            self::SQUAD_LEADER => 'Squad Leader',
            self::PLATOON_LEADER => 'Platoon Leader',
            self::GENERAL_SERGEANT => 'General Sergeant',
            self::EXECUTIVE_OFFICER => 'Executive Officer',
            self::COMMANDING_OFFICER => 'Commanding Officer',
            self::CLAN_ADMIN => 'Clan Admin',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::MEMBER,
            self::GENERAL_SERGEANT => '',

            self::SQUAD_LEADER => 'fas fa-shield-alt',
            self::PLATOON_LEADER => 'fas fa-dot-circle',
            self::EXECUTIVE_OFFICER => 'fas fa-circle-notch',
            self::COMMANDING_OFFICER => 'fas fa-circle',
            self::CLAN_ADMIN => 'fas fa-square',
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            self::MEMBER,
            self::GENERAL_SERGEANT => 'text-default',

            self::SQUAD_LEADER => 'text-info',
            self::PLATOON_LEADER => 'text-warning',

            self::EXECUTIVE_OFFICER,
            self::COMMANDING_OFFICER,
            self::CLAN_ADMIN => 'text-danger',
        };
    }
}
