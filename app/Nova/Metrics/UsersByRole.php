<?php

namespace App\Nova\Metrics;

use App\Models\User;
use DateInterval;
use DateTimeInterface;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class UsersByRole extends Partition
{
    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->count($request, User::class, 'role_id');
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return DateInterval|DateTimeInterface|float|int
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
        return 'members-by-role';
    }
}
