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

    case RECRUIT             = 1;
    case CADET               = 2;
    case PRIVATE             = 3;
    case PRIVATE_FIRST_CLASS = 4;
    case SPECIALIST          = 5;
    case TRAINER             = 6;
    case LANCE_CORPORAL      = 7;
    case CORPORAL            = 8;
    case SERGEANT            = 9;
    case STAFF_SERGEANT      = 10;
    case MASTER_SERGEANT     = 11;
    case FIRST_SERGEANT      = 12;
    case COMMAND_SERGEANT    = 13;
    case SERGEANT_MAJOR      = 14;

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
            self::CORPORAL         => '#B8C0C8',
            self::SERGEANT         => '#00FF00',
            self::STAFF_SERGEANT   => '#2E2EFE',
            self::MASTER_SERGEANT  => '#CC00FF',
            self::FIRST_SERGEANT   => '#00FFFF',
            self::COMMAND_SERGEANT => '#FFFF00',
            self::SERGEANT_MAJOR   => '#F09C58',
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
            self::RECRUIT             => 'Rct',
            self::CADET               => 'Cdt',
            self::PRIVATE             => 'Pvt',
            self::PRIVATE_FIRST_CLASS => 'Pfc',
            self::SPECIALIST          => 'Spec',
            self::TRAINER             => 'Tr',
            self::LANCE_CORPORAL      => 'LCpl',
            self::CORPORAL            => 'Cpl',
            self::SERGEANT            => 'Sgt',
            self::STAFF_SERGEANT      => 'SSgt',
            self::MASTER_SERGEANT     => 'MSgt',
            self::FIRST_SERGEANT      => '1stSgt',
            self::COMMAND_SERGEANT    => 'CmdSgt',
            self::SERGEANT_MAJOR      => 'SgtMaj',
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

    public static function getAllRanksWithLabels(): array
    {
        $ranks = [];
        foreach (self::cases() as $rank) {
            $ranks[$rank->getId()] = $rank->getLabel();
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

    public function isSeniorLeader(): bool
    {
        return $this->value >= self::MASTER_SERGEANT->value;
    }

    public function getTier(): string
    {
        return match ($this) {
            self::SERGEANT_MAJOR   => 'mythic',
            self::COMMAND_SERGEANT => 'legendary',
            self::FIRST_SERGEANT   => 'epic',
            self::MASTER_SERGEANT  => 'rare-plus',
            self::STAFF_SERGEANT   => 'rare',
            self::SERGEANT         => 'uncommon',
            self::CORPORAL,
            self::LANCE_CORPORAL => 'officer',
            default              => 'enlisted',
        };
    }

    public function getGroup(): string
    {
        if ($this->isSeniorLeader()) {
            return 'admin';
        }
        if ($this->isOfficer()) {
            return 'officer';
        }

        return 'enlisted';
    }

    public function getDuties(): string
    {
        return match ($this) {
            self::SERGEANT_MAJOR      => 'Overall leader of Clan AOD, responsible for handling issues within the clan and representing AOD when working with other clan leaders. All major decisions, policy changes, and high-ranking promotions must pass through the Sergeant Major.',
            self::COMMAND_SERGEANT    => 'High-ranking clan leader who assists the SgtMajs in the leadership of AOD. Instructs clan management across all divisions, supports lower-ranking officers with division issues, and is involved in forum administration, division creation and deletion, Sergeant promotions, and policy changes.',
            self::FIRST_SERGEANT      => 'High-ranking admin who oversees a number of divisions, assisting members of all ranks. First Sergeants are expected to aid in division decisions and leadership, and to participate in clan-wide decision making.',
            self::MASTER_SERGEANT     => 'Experienced Sergeant active across a number of divisions, serving as informant and advisor. Master Sergeants also oversee the communication of all Sergeant promotions.',
            self::STAFF_SERGEANT      => 'Experienced Sergeant, commonly serving as CO or XO. Staff Sergeants are expected to aid newer Sergeants and lower-ranking officers. To be eligible for promotion, a Staff Sergeant should be active in more than one division, acting as an advisor and informant when requested by AOD leadership.',
            self::SERGEANT            => 'Member who has recently received the Sergeant rank. Acts as a squad leader within their full-time division. Upon reaching Sergeant, members gain access to ClanAOD.net and AOD TeamSpeak moderation tools, and become eligible for the Commanding Officer position.',
            self::CORPORAL            => 'Division squad leader. Not yet permitted to promote independently, a Corporal is responsible for their five recruits and leads their squad under their Sergeant\'s guidance. During this period, the Corporal is trained toward the Sergeant rank, learning squad management alongside their Sgt.',
            self::LANCE_CORPORAL      => 'Experienced member. Sergeants within divisions begin training Lance Corporals for the leadership roles they will assume upon promotion to Corporal. LCpls assist squad leaders with tasks as needed and continue helping their recruits settle into the clan.',
            self::TRAINER             => '[Division Optional] An experienced member who has demonstrated individual ability and has been granted preliminary officer roles to begin recruiting. Their primary focus is helping all new members within their division become productive contributors to the clan.',
            self::SPECIALIST          => 'Experienced member with demonstrated skill and game knowledge. A well-tenured member with broad clan experience — the rank most called upon to mentor others within their division.',
            self::PRIVATE_FIRST_CLASS => 'Expected to help fellow clan members and set an example for incoming members. Rank is based on merit and time served. At this stage, a member should know and follow the Code of Conduct and strive to be the best representation of AOD they can be.',
            self::PRIVATE             => 'Rank based on merit and time in AOD. A Private should post regularly on the forums and consistently demonstrate clan loyalty and honor.',
            self::CADET               => 'Active member who has demonstrated loyalty and honor in their first months in the clan.',
            self::RECRUIT             => 'New member of AOD. Should begin learning the Code of Conduct and the clan rank system, and be active in both the division\'s AOD server and on the forums.',
        };
    }

    public function routeNotificationForAdmin()
    {
        return config('aod.msgt-channel');
    }

    public static function canManageComments($targetRank): bool {}
}
