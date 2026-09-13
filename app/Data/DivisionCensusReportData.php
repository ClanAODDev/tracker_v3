<?php

namespace App\Data;

use App\Models\Division;
use Illuminate\Support\Collection;

class DivisionCensusReportData
{
    private Collection $censuses;

    private array $stats;

    public function __construct(private Division $division)
    {
        $this->censuses = $division->census
            ->sortByDesc('created_at')
            ->unique(fn ($c) => $c->created_at->toDateString())
            ->take(52)
            ->values();

        $latest   = $this->censuses->first();
        $previous = $this->censuses->skip(1)->first();

        $this->stats = [
            'population'   => $latest?->count ?? 0,
            'voicePercent' => $latest && $latest->count > 0
                ? round($latest->weekly_voice_count / $latest->count * 100, 1)
                : 0,
            'popChange'   => $latest && $previous ? $latest->count - $previous->count : 0,
            'voiceChange' => 0,
            'avgVoice'    => 0,
        ];

        if ($previous && $previous->count > 0 && $latest) {
            $prevVoice                  = round($previous->weekly_voice_count / $previous->count * 100, 1);
            $this->stats['voiceChange'] = round($this->stats['voicePercent'] - $prevVoice, 1);
        }

        $recentWithPop = $this->censuses->take(4)->filter(fn ($c) => $c->count > 0);
        if ($recentWithPop->count() > 0) {
            $this->stats['avgVoice'] = round($recentWithPop->avg(fn ($c) => $c->weekly_voice_count / $c->count * 100), 1);
        }

        $peakCensus = $division->census
            ->unique(fn ($c) => $c->created_at->toDateString())
            ->sortByDesc('count')
            ->first();

        $this->stats['peakCount'] = $peakCensus?->count ?? 0;
        $this->stats['peakDate']  = $peakCensus?->created_at?->format('M j, Y');
    }

    public static function for(Division $division): self
    {
        return new self($division);
    }

    public function toArray(): array
    {
        $censuses = $this->censuses;
        $ordered  = $censuses->reverse()->values();

        return [
            'division' => ['name' => $this->division->name, 'slug' => $this->division->slug],
            'stats'    => $this->stats,
            'series'   => $ordered->map(fn ($census) => [
                'date'       => $census->created_at->format('M j'),
                'population' => $census->count,
                'voice'      => $census->weekly_voice_count,
            ])->values(),
            'weeks' => $censuses->map(function ($census, $index) use ($censuses) {
                $prev         = $censuses->skip($index + 1)->first();
                $voicePercent = $census->count > 0
                    ? round($census->weekly_voice_count / $census->count * 100, 1)
                    : 0;

                return [
                    'date'         => $census->created_at->format('M j, Y'),
                    'population'   => $census->count,
                    'change'       => $prev ? $census->count - $prev->count : 0,
                    'voicePercent' => $voicePercent,
                    'voiceCount'   => $census->weekly_voice_count,
                ];
            })->values(),
        ];
    }
}
