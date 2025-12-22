<?php

namespace App\Filament\Mod\Widgets;

use App\Models\Census;
use App\Models\Division;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ActivityTrendsWidget extends ChartWidget
{
    protected static ?string $heading = 'Activity Trends (Last 30 Days)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

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

        $censuses = Census::where('division_id', $division->id)
            ->orderBy('created_at')
            ->take(30)
            ->get();

        $labels = $censuses->map(fn ($c) => $c->created_at->format('M j'))->toArray();
        $population = $censuses->pluck('count')->toArray();
        $weeklyActive = $censuses->pluck('weekly_active_count')->toArray();
        $weeklyVoice = $censuses->pluck('weekly_voice_count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Population',
                    'data' => $population,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Weekly Active',
                    'data' => $weeklyActive,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Weekly Voice',
                    'data' => $weeklyVoice,
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    protected function getDivision(): ?Division
    {
        return Auth::user()?->division;
    }
}
