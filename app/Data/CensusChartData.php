<?php

namespace App\Data;

use App\Models\Division;

readonly class CensusChartData
{
    public function __construct(
        public array $labels,
        public array $population,
        public array $voiceActive,
    ) {}

    public static function fromDivision(Division $division, int $weeks = 52): self
    {
        $census = $division->census()
            ->orderByDesc('created_at')
            ->take($weeks)
            ->get()
            ->reverse()
            ->values();

        $restartIndex = 0;
        for ($i = 1; $i < $census->count(); $i++) {
            $gap = $census[$i - 1]->created_at->diffInDays($census[$i]->created_at);
            if ($gap > 28) {
                $restartIndex = $i;
            }
        }

        $census = $census->slice($restartIndex)->values();

        return new self(
            labels: $census->map(fn ($c) => $c->created_at->format('M j'))->toArray(),
            population: $census->pluck('count')->toArray(),
            voiceActive: $census->pluck('weekly_voice_count')->toArray(),
        );
    }

    public function toArray(): array
    {
        return [
            'labels'      => $this->labels,
            'population'  => $this->population,
            'voiceActive' => $this->voiceActive,
        ];
    }
}
