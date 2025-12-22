<?php

namespace App\Enums;

enum TagVisibility: string
{
    case PUBLIC = 'public';
    case OFFICERS = 'leadership';
    case SENIOR_LEADERS = 'senior_leader';

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Public',
            self::OFFICERS => 'Officers Only',
            self::SENIOR_LEADERS => 'Senior Leader Only',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PUBLIC => 'Visible to everyone',
            self::OFFICERS => 'Only visible to officers',
            self::SENIOR_LEADERS => 'Only visible to senior leaders',
        };
    }
}
