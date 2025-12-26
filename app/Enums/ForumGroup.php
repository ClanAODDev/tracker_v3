<?php

namespace App\Enums;

enum ForumGroup: int
{
    case BANNED = 49;
    case ADMIN = 6;
    case SERGEANT = 52;
    case STAFF_SERGEANT = 66;
    case DIVISION_CO = 80;
    case DIVISION_XO = 79;

    public static function seniorLeaderGroups(): array
    {
        return [
            self::SERGEANT->value,
            self::STAFF_SERGEANT->value,
            self::DIVISION_CO->value,
            self::DIVISION_XO->value,
        ];
    }
}
