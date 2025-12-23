<?php

namespace App\Data;

use Carbon\Carbon;

readonly class TenureData
{
    public function __construct(
        public int $years,
        public int $months,
        public int $totalDays,
        public ?Carbon $joinDate,
    ) {}

    public static function fromMember(\App\Models\Member $member): self
    {
        $years = $member->join_date ? (int) floor($member->join_date->diffInYears()) : 0;
        $months = $member->join_date ? (int) $member->join_date->diffInMonths() % 12 : 0;
        $totalDays = $member->join_date ? (int) $member->join_date->diffInDays() : 0;

        return new self(
            years: $years,
            months: $months,
            totalDays: $totalDays,
            joinDate: $member->join_date,
        );
    }
}
