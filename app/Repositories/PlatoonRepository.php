<?php

namespace App\Repositories;

use Carbon\CarbonImmutable;

class PlatoonRepository
{
    private array $labels = [
        'Current', '14 days', '30-60 days', 'More than 60 days',
    ];

    private array $colors = [
        '#28b62c', '#ff851b', '#ff4136', '#000',
    ];

    public function __construct()
    {
        $this->twoWeeksAgo = CarbonImmutable::now()->subDays(14);
        $this->oneMonthAgo = CarbonImmutable::now()->subDays(30);
        $this->twoMonthsAgo = CarbonImmutable::now()->subDays(60);
    }

    public function getPlatoonForumActivity(\App\Models\Platoon $platoon): array
    {
        return $this->getActivityFor('last_activity', $platoon);
    }

    public function getPlatoonTSActivity(\App\Models\Platoon $platoon): array
    {
        return $this->getActivityFor('last_ts_activity', $platoon);
    }

    private function getActivityFor(string $string, $platoon): array
    {
        $twoWeeks = $platoon->members()
            ->where($string, '>=', $this->twoWeeksAgo)->count();

        $oneMonth = $platoon->members()->where($string, '<=', $this->twoWeeksAgo)
            ->where($string, '>=', $this->oneMonthAgo)->count();

        $moreThanOneMonth = $platoon->members()
            ->whereBetween($string, [
                $this->oneMonthAgo->subDays(60), $this->oneMonthAgo,
            ])->count();

        $moreThan60Days = $platoon->members()
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
