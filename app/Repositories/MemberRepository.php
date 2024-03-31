<?php

namespace App\Repositories;

use App\Models\Member;
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
     * form of year and month.
     *
     * @return static
     */
    public function promotionPeriods()
    {
        return collect(DB::select(
            DB::raw("SELECT Year(last_promoted_at) AS year, MONTHNAME(STR_TO_DATE(Month(last_promoted_at), '%m')) AS month
                      FROM members GROUP BY Year(last_promoted_at), Month(last_promoted_at)
                      ORDER BY year DESC
            ")
        ))
            ->filter(fn ($values) => $values->month !== null);
    }
}
