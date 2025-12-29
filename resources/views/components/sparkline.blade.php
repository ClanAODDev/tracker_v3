@props(['data', 'width' => 60, 'height' => 20, 'color' => null, 'trend' => null])

@php
    $points = collect($data);
    if ($points->isEmpty() || $points->count() < 2) {
        return;
    }

    $min = $points->min();
    $max = $points->max();
    $range = $max - $min ?: 1;

    $padding = 2;
    $chartWidth = $width - ($padding * 2);
    $chartHeight = $height - ($padding * 2);

    $stepX = $chartWidth / ($points->count() - 1);

    $pathPoints = $points->map(function ($value, $index) use ($min, $range, $stepX, $chartHeight, $padding) {
        $x = $padding + ($index * $stepX);
        $y = $padding + $chartHeight - (($value - $min) / $range * $chartHeight);
        return round($x, 1) . ',' . round($y, 1);
    })->implode(' ');

    if ($trend === null) {
        $first = $points->first();
        $last = $points->last();
        if ($last > $first) {
            $trend = 'up';
        } elseif ($last < $first) {
            $trend = 'down';
        } else {
            $trend = 'neutral';
        }
    }

    $strokeColor = $color ?? match($trend) {
        'up' => 'var(--color-success)',
        'down' => 'var(--color-danger)',
        default => 'var(--color-gray-400)',
    };
@endphp

<svg class="sparkline" width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}">
    <polyline
        fill="none"
        stroke="{{ $strokeColor }}"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
        points="{{ $pathPoints }}"
    />
</svg>
