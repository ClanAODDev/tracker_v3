<?php

namespace App\Enums;

enum ForumGroup: int
{
    case REGISTERED_USER             = 2;
    case AWAITING_EMAIL_CONFIRMATION = 3;
    case AWAITING_MODERATION         = 4;
    case ADMIN                       = 6;
    case BANNED                      = 49;
    case MEMBER                      = 50;
    case SERGEANT                    = 52;
    case STAFF_SERGEANT              = 66;
    case DIVISION_XO                 = 79;
    case DIVISION_CO                 = 80;

    public function isEligibleForRecruitment(): bool
    {
        return in_array($this, [
            self::REGISTERED_USER,
            self::AWAITING_MODERATION,
        ]);
    }

    public function recruitmentRejectionReason(): ?string
    {
        return match ($this) {
            self::AWAITING_EMAIL_CONFIRMATION => 'User has not verified their email',
            self::BANNED                      => 'User forum account is banned',
            self::MEMBER                      => 'User is already an AOD member',
            default                           => $this->isEligibleForRecruitment() ? null : "User is in forum group: {$this->name}",
        };
    }

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
