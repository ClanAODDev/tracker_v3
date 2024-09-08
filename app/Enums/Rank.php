<?php

namespace App\Enums;

use App\Traits\EnumOptions;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum Rank: int implements HasLabel
{
    use EnumOptions;

    case RECRUIT = 1;
    case CADET = 2;
    case PRIVATE = 3;
    case PRIVATE_FIRST_CLASS = 4;
    case SPECIALIST = 5;
    case TRAINER = 6;
    case LANCE_CORPORAL = 7;
    case CORPORAL = 8;
    case SERGEANT = 9;
    case STAFF_SERGEANT = 10;
    case MASTER_SERGEANT = 11;
    case FIRST_SERGEANT = 12;
    case COMMAND_SERGEANT = 13;
    case SERGEANT_MAJOR = 14;

    public function getLabel(): ?string
    {
        return Str::of($this->name)
            ->replace('_', '')
            ->title();
    }

    public function getAbbreviation(): string
    {
        return match ($this) {
            self::RECRUIT => 'Rct',
            self::CADET => 'Cdt',
            self::PRIVATE => 'Pvt',
            self::PRIVATE_FIRST_CLASS => 'Pfc',
            self::SPECIALIST => 'Spec',
            self::TRAINER => 'Tr',
            self::LANCE_CORPORAL => 'LCpl',
            self::CORPORAL => 'Cpl',
            self::SERGEANT => 'Sgt',
            self::STAFF_SERGEANT => 'SSgt',
            self::MASTER_SERGEANT => 'MSgt',
            self::FIRST_SERGEANT => '1stSgt',
            self::COMMAND_SERGEANT => 'CmdSgt',
            self::SERGEANT_MAJOR => 'SgtMaj',
        };
    }
}
