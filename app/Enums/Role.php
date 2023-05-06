<?php

namespace App\Enums;

enum Role:int
{
    // @TODO: convert ids to abbreviations
    // and get rid of the abbreviation helper
    case MEMBER = 1;
    case OFFICER = 2;
    case JUNIOR_LEADER = 3;
    case SENIOR_LEADER = 4;
    case ADMINISTRATOR = 5;
    case BANNED = 6;

    public function name(): string
    {
        return \Str::title(
            str_replace('_', ' ', $this->name)
        );
    }

    // might not need this
    public function label(): string
    {
        return match ($this) {
            self::MEMBER,
            self::OFFICER,
            self::JUNIOR_LEADER  => 'text-default',
            self::SENIOR_LEADER => 'text-warning',
            self::ADMINISTRATOR => 'text-danger',
        };
    }
}
