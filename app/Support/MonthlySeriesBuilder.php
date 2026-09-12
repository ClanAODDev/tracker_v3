<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Turns a sparse, date-range-filtered query result (one row per month that
 * actually had activity) into a gap-free monthly series suitable for a chart —
 * every month in range appears exactly once, in order, defaulting to zero.
 */
class MonthlySeriesBuilder
{
    /**
     * Build an ordered, gap-free list of months between `$start` and `$end`
     * (inclusive), capped at the current month so a range extending into the
     * future doesn't produce trailing empty buckets.
     *
     * @return Collection<int, array{bucket: string, date: string}>
     */
    public static function months(Carbon $start, Carbon $end): Collection
    {
        $months       = collect();
        $current      = $start->copy()->startOfMonth();
        $endMonth     = $end->copy()->startOfMonth();
        $currentMonth = now()->startOfMonth();

        if ($endMonth->gt($currentMonth)) {
            $endMonth = $currentMonth;
        }

        while ($current->lte($endMonth)) {
            $months->push(['bucket' => $current->format('Y-m'), 'date' => $current->format('M y')]);
            $current->addMonth();
        }

        return $months;
    }

    /**
     * Zero-fill a raw, sparse "one row per bucket that had data" result set
     * against a full month range, returning `[label, value]` pairs in order.
     *
     * @param  Collection<int, array{bucket: string, date: string}>  $months
     * @param  Collection<int, object{bucket: string}>  $rawRows  each row must expose `bucket` and `$column`
     */
    public static function fill(Collection $months, Collection $rawRows, string $column): Collection
    {
        $keyed = $rawRows->keyBy('bucket');

        return $months->map(fn ($m) => [
            $m['date'],
            $keyed->has($m['bucket']) ? $keyed->get($m['bucket'])->$column : 0,
        ]);
    }
}
