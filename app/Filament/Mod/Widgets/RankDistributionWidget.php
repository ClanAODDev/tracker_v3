<?php

namespace App\Filament\Mod\Widgets;

use App\Enums\Rank;
use App\Models\Division;
use App\Models\Member;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class RankDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'Rank Distribution';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $division = $this->getDivision();

        if (! $division) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $rankCounts = Member::where('division_id', $division->id)
            ->selectRaw('rank, COUNT(*) as count')
            ->groupBy('rank')
            ->orderBy('rank')
            ->pluck('count', 'rank')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [];

        $rankColors = [
            Rank::RECRUIT->value => '#ef4444',
            Rank::CADET->value => '#f97316',
            Rank::PRIVATE->value => '#f59e0b',
            Rank::PRIVATE_FIRST_CLASS->value => '#eab308',
            Rank::SPECIALIST->value => '#84cc16',
            Rank::TRAINER->value => '#22c55e',
            Rank::LANCE_CORPORAL->value => '#10b981',
            Rank::CORPORAL->value => '#14b8a6',
            Rank::SERGEANT->value => '#06b6d4',
            Rank::STAFF_SERGEANT->value => '#0ea5e9',
            Rank::MASTER_SERGEANT->value => '#3b82f6',
            Rank::FIRST_SERGEANT->value => '#6366f1',
            Rank::COMMAND_SERGEANT->value => '#8b5cf6',
            Rank::SERGEANT_MAJOR->value => '#a855f7',
        ];

        foreach (Rank::cases() as $rank) {
            $count = $rankCounts[$rank->value] ?? 0;
            if ($count > 0) {
                $labels[] = $rank->getAbbreviation();
                $data[] = $count;
                $colors[] = $rankColors[$rank->value];
            }
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }

    protected function getDivision(): ?Division
    {
        return Auth::user()?->division;
    }
}
