<?php

namespace App\Repositories;

use App\Models\Squad;
use Carbon\CarbonImmutable;

class SquadRepository
{
    private array $labels = [
        'Current', '14 days', '30-60 days', 'More than 60 days',
    ];

    private array $colors = [
        '#28b62c', '#ff851b', '#ff4136', '#000',
    ];

    private CarbonImmutable $twoWeeksAgo;

    private CarbonImmutable $oneMonthAgo;

    private CarbonImmutable $twoMonthsAgo;

    public function __construct()
    {
        $this->twoWeeksAgo = CarbonImmutable::now()->subDays(14);
        $this->oneMonthAgo = CarbonImmutable::now()->subDays(30);
        $this->twoMonthsAgo = CarbonImmutable::now()->subDays(60);
    }

    public function getSquadForumActivity(Squad $squad): array
    {
        return $this->getActivityFor('last_activity', $squad);
    }

    public function getSquadTSActivity(Squad $squad): array
    {
        return $this->getActivityFor('last_ts_activity', $squad);
    }

    public function getSquadVoiceActivity(Squad $squad): array
    {
        return $this->getActivityFor('last_voice_activity', $squad);
    }

    private function getActivityFor(string $string, $squad): array
    {
        $twoWeeks = $squad->members()
            ->where($string, '>=', $this->twoWeeksAgo)->count();

        $oneMonth = $squad->members()->where($string, '<=', $this->twoWeeksAgo)
            ->where($string, '>=', $this->oneMonthAgo)->count();

        $moreThanOneMonth = $squad->members()
            ->whereBetween($string, [
                $this->oneMonthAgo->subDays(60), $this->oneMonthAgo,
            ])->count();

        $moreThan60Days = $squad->members()
            ->where($string, '<=', $this->twoMonthsAgo)
            ->count();

        return [
            'labels' => $this->labels,
            'values' => [
                $twoWeeks,
                $oneMonth,
                $moreThanOneMonth,
                $moreThan60Days,
            ],
            'colors' => $this->colors,
        ];
    }
}
