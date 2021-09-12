<?php

namespace App\Nova\Filters;

use App\Models\Division;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ByDivision extends Filter
{
    /**
     * Apply the filter to the given query.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->where('division_id', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return Division::active()
            ->orderBy('name')
            ->pluck('id', 'name')
            ->toArray();
    }
}
