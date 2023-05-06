<?php

namespace App\Enums;

enum Role:int
{
    case MEMBER = 1;
    case OFFICER = 2;
    case JUNIOR_LEADER = 3;
    case SENIOR_LEADER = 4;
    case ADMINISTRATOR = 5;

    public function name(): string
    {
        return \Str::title(
            str_replace('_', ' ', $this->name)
        );
    }

    // replace roleLabelColored with this
    // role presenter can go away, as can role table
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
