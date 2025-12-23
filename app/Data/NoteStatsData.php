<?php

namespace App\Data;

use Illuminate\Support\Collection;

readonly class NoteStatsData
{
    public function __construct(
        public int $total,
        public int $positive,
        public int $negative,
        public int $misc,
        public int $sr_ldr,
        public ?string $latestType,
    ) {}

    public static function fromNotes(Collection $notes): self
    {
        $latestNote = $notes->sortByDesc('created_at')->first();

        return new self(
            total: $notes->count(),
            positive: $notes->where('type', 'positive')->count(),
            negative: $notes->where('type', 'negative')->count(),
            misc: $notes->where('type', 'misc')->count(),
            sr_ldr: $notes->where('type', 'sr_ldr')->count(),
            latestType: $latestNote?->type,
        );
    }
}
