<?php

namespace App\Data;

use Illuminate\Support\Collection;

readonly class AwardsData
{
    public function __construct(
        public int $total,
        public Collection $byRarity,
    ) {}

    public static function fromAwards(Collection $awards): self
    {
        return new self(
            total: $awards->count(),
            byRarity: $awards->groupBy(fn ($a) => $a->award->getRarity())->map->count(),
        );
    }
}
