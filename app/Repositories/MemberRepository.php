<?php

namespace App\Repositories;

use App\Member;
use Illuminate\Support\Facades\DB;

class MemberRepository
{

    public function search($name)
    {
        return Member::where('name', 'LIKE', "%{$name}%")
            ->with('rank')
            ->get();
    }

    /**
     * Returns promotion periods for members in the
     * form of year and month
     *
     * @return static
     */
    public function promotionPeriods()
    {
        return collect(DB::select(
            DB::raw(" SELECT Year(last_promoted) AS year, MONTHNAME(STR_TO_DATE(Month(last_promoted), '%m')) AS month
                      FROM members GROUP BY Year(last_promoted), Month(last_promoted)
                      ORDER BY year DESC
            ")))
            ->filter(function ($values) {
                return $values->month !== null;
            });
    }

    public function staffSergeants()
    {
        return Member::where('rank_id', 10)->get();
    }
}
