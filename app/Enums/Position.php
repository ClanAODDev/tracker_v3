<?php

namespace App\Enums;

enum Position:int
{
    case MEMBER = 1;
    case SQUAD_LEADER = 2;
    case PLATOON_LEADER = 3;
    case GENERAL_SERGEANT = 4;
    case EXECUTIVE_OFFICER = 5;
    case COMMANDING_OFFICER = 6;
    case CLAN_ADMIN = 7;

    public function name(): string
    {
        return \Str::title(
            str_replace('_', ' ', $this->name)
        );
    }

    public function icon(): string
    {
        return match($this) {
            self::MEMBER,
            self::GENERAL_SERGEANT => '',

            self::SQUAD_LEADER => 'fas fa-shield-alt',
            self::PLATOON_LEADER => 'fas fa-dot-circle',
            self::EXECUTIVE_OFFICER => 'fas fa-circle-notch',
            self::COMMANDING_OFFICER => 'fas fa-circle',
            self::CLAN_ADMIN => 'fas fa-square',
        };
    }

    public function class(): string
    {
        return match($this) {
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