<?php

namespace App\Nova\Metrics;

use App\Models\Member;
use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;

class MembersByMonth extends Trend
{
    /**
     * Calculate the value of the metric.
     *
     * @param Request $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->countByMonths($request, Member::class, 'join_date');
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            6 => '6 Months',
            12 => '1 Year',
            24 => '2 Years',
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  DateTimeInterface|DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'members-by-day';
    }
}
