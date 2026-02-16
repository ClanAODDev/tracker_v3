<?php

namespace App\Enums;

use App\Traits\EnumOptions;
use Filament\Support\Contracts\HasLabel;

enum Position: int implements HasLabel
{
    use EnumOptions;

    case MEMBER             = 1;
    case SQUAD_LEADER       = 2;
    case PLATOON_LEADER     = 3;
    case EXECUTIVE_OFFICER  = 5;
    case COMMANDING_OFFICER = 6;
    case CLAN_ADMIN         = 7;

    public function getLabel(): ?string
    {
        return ucwords(str_replace('_', ' ', strtolower($this->name)));
    }

    protected static function getValues(): array
    {
        return [
            self::MEMBER->value,
            self::SQUAD_LEADER->value,
            self::PLATOON_LEADER->value,
            self::EXECUTIVE_OFFICER->value,
            self::COMMANDING_OFFICER->value,
            self::CLAN_ADMIN->value,
        ];
    }

    public function getAbbreviation(): ?string
    {
        return match ($this) {
            self::SQUAD_LEADER       => 'SL',
            self::PLATOON_LEADER     => 'PL',
            self::EXECUTIVE_OFFICER  => 'XO',
            self::COMMANDING_OFFICER => 'CO',
            self::CLAN_ADMIN         => 'CA',

            self::MEMBER => '',
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            self::MEMBER         => 'text-default',
            self::SQUAD_LEADER   => 'text-info',
            self::PLATOON_LEADER => 'text-warning',

            self::EXECUTIVE_OFFICER,
            self::COMMANDING_OFFICER,
            self::CLAN_ADMIN => 'text-danger',
        };
    }
}
