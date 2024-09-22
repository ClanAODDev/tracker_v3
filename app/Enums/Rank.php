<?php

namespace App\Enums;

use App\Traits\EnumOptions;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum Rank: int implements HasLabel, HasColor
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
            ->replace('_', ' ')
            ->title();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::RECRUIT => 'gray',
            self::CADET => 'gray',
            self::PRIVATE => 'gray',
            self::PRIVATE_FIRST_CLASS => 'gray',
            self::SPECIALIST => 'gray',
            self::TRAINER => 'gray',
            self::LANCE_CORPORAL => 'gray',
            self::CORPORAL => 'gray',
            self::SERGEANT => 'success',
            self::STAFF_SERGEANT => 'success',
            self::MASTER_SERGEANT => 'warning',
            self::FIRST_SERGEANT => 'warning',
            self::COMMAND_SERGEANT => 'warning',
            self::SERGEANT_MAJOR => 'warning',
        };
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

    public function getId(): int
    {
        return array_search($this, self::cases()) + 1;
    }

    public static function getAllRanks(): array
    {
        $ranks = [];
        foreach (self::cases() as $rank) {
            $ranks[$rank->getId()] = $rank->getAbbreviation();
        }

        return $ranks;
    }
}
