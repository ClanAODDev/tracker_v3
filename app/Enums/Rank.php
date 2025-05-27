<?php

namespace App\Enums;

use App\Traits\EnumOptions;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

enum Rank: int implements HasColor, HasLabel
{
    use EnumOptions;
    use Notifiable;

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

    public function getColorHex(): string
    {
        return match ($this) {
            self::RECRUIT,
            self::CADET,
            self::PRIVATE,
            self::PRIVATE_FIRST_CLASS,
            self::SPECIALIST,
            self::TRAINER,
            self::LANCE_CORPORAL,
            self::CORPORAL => '#c80909',
            self::SERGEANT => '#00FF00',
            self::STAFF_SERGEANT => '#2E2EFE',
            self::MASTER_SERGEANT => '#CC00FF',
            self::FIRST_SERGEANT => '#00FFFF',
            self::COMMAND_SERGEANT => '#FFFF00',
            self::SERGEANT_MAJOR => '#F09C58',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::RECRUIT,
            self::CADET,
            self::PRIVATE,
            self::PRIVATE_FIRST_CLASS,
            self::SPECIALIST,
            self::TRAINER,
            self::LANCE_CORPORAL,
            self::CORPORAL => 'gray',

            self::SERGEANT,
            self::STAFF_SERGEANT => 'success',

            self::MASTER_SERGEANT,
            self::FIRST_SERGEANT,
            self::COMMAND_SERGEANT,
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

    public function isPromotion(Rank $previousRank): bool
    {
        return $this->value > $previousRank->value;
    }

    public function isOfficer(): bool
    {
        return $this->value >= self::LANCE_CORPORAL->value;
    }

    public function routeNotificationForAdmin()
    {
        return config('app.aod.msgt-channel');
    }

    public static function canManageComments($targetRank): bool {}
}
