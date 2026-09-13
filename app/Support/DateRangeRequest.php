<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Reads an optional `start`/`end` date-range pair off the current request,
 * falling back to caller-supplied defaults, and applies it to a query.
 */
class DateRangeRequest
{
    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: array{start: string, end: string}}
     */
    public static function parse(?Carbon $defaultStart = null, ?Carbon $defaultEnd = null): array
    {
        $start = request()->filled('start')
            ? Carbon::parse(request('start'))->startOfDay()
            : $defaultStart;

        $end = request()->filled('end')
            ? Carbon::parse(request('end'))->endOfDay()
            : $defaultEnd;

        $range = [
            'start' => request('start') ?? $defaultStart?->format('Y-m-d') ?? '',
            'end'   => request('end') ?? $defaultEnd?->format('Y-m-d') ?? '',
        ];

        return [$start, $end, $range];
    }

    public static function applyTo($query, ?Carbon $start, ?Carbon $end, string $column = 'created_at')
    {
        if ($start) {
            $query->where($column, '>=', $start);
        }

        if ($end) {
            $query->where($column, '<=', $end);
        }

        return $query;
    }
}
