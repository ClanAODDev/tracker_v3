<?php

namespace App\Data;

use Illuminate\Support\Collection;

readonly class RecruitingData
{
    public function __construct(
        public int $total,
        public int $active,
        public int $inactive,
        public ?int $retentionRate,
    ) {}

    public static function fromRecruits(Collection $recruits): self
    {
        $total = $recruits->count();
        $active = $recruits->filter(fn ($r) => $r->division_id !== 0)->count();
        $retentionRate = $total > 0 ? (int) round($active / $total * 100) : null;

        return new self(
            total: $total,
            active: $active,
            inactive: $total - $active,
            retentionRate: $retentionRate,
        );
    }
}
