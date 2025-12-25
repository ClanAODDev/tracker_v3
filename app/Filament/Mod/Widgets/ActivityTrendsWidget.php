<?php

namespace App\Filament\Mod\Widgets;

use App\Models\Census;
use App\Models\Division;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ActivityTrendsWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '30' => '30 Days',
            '90' => '90 Days',
            '365' => '1 Year',
        ];
    }

    public function getHeading(): string
    {
        $days = $this->filter ?? '30';
        $label = match ($days) {
            '365' => '1 Year',
            default => "{$days} Days",
        };

        return "Activity Trends (Last {$label})";
    }

    protected function getData(): array
    {
        $division = $this->getDivision();

        if (! $division) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $days = (int) ($this->filter ?? 30);

        $censuses = Census::where('division_id', $division->id)
            ->latest('created_at')
            ->take($days)
            ->get()
            ->reverse()
            ->values();

        $labels = $censuses->map(fn ($c) => $c->created_at->format('M j'))->toArray();
        $population = $censuses->pluck('count')->toArray();
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
