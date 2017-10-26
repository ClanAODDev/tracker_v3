<?php

namespace App\Http\Controllers\Division;

use App\Division;
use App\Repositories\MemberRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'activeDivision']);
    }

    public function retentionReport(Division $division)
    {

    }

    /**
     * @param MemberRepository $repository
     * @param $division
     * @param null $month
     * @param null $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function promotionsReport(MemberRepository $repository, $division, $month = null, $year = null)
    {
        try {
            $members = $this->getMemberPromotions($division, $month, $year);
        } catch (\Exception $exception) {
            $members = [];
        }

        $ranks = $members->pluck('rank.abbreviation')->unique();
        $counts = $members->groupBy('rank_id')->each(function ($rank) {
            $rank->count = count($rank);
        })->pluck('count');

        $promotionPeriods = $repository->promotionPeriods();

        return view('division.reports.promotions', compact(
            'members', 'division', 'promotionPeriods', 'year', 'month',
            'ranks', 'counts'));
    }

    /**
     * @param $division
     * @param $month
     * @param $year
     * @return mixed
     */
    private function getMemberPromotions($division, $month, $year)
    {
        $dates = ($month && $year) ? [
            Carbon::parse($month . " {$year}")->startOfMonth(),
            Carbon::parse($month . " {$year}")->endOfMonth()
        ] : [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ];

        $members = $division->members()
            ->with('rank')
            ->whereBetween('last_promoted', $dates)
            ->orderByDesc('rank_id')->get();

        return $members;
    }

    /**
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tsReport(Division $division)
    {
        $issues = $division->mismatchedTSMembers;

        return view('division.reports.ts-report', compact('division', 'issues'));
    }

    /**
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function censusReport(Division $division)
    {
        $censuses = $division->census->sortByDesc('created_at')->take(52);

        $populations = $censuses->values()->map(function ($census, $key) {
            return [$census->javascriptTimestamp, $census->count];
        });

        $weeklyActive = $censuses->values()->map(function ($census, $key) {
            return [$census->javascriptTimestamp, $census->weekly_active_count];
        });

        $weeklyTsActive = $censuses->values()->map(function ($census, $key) {
            return [$census->javascriptTimestamp, $census->weekly_ts_count];
        });

        $comments = $censuses->values()
            ->filter(function ($census) use ($censuses) {
                return ($census->notes);
            })->map(function ($census, $key) use ($censuses) {

                return [
                    'x' => $key,
                    'y' => $censuses->values()->pluck('count'),
                    'contents' => $census->notes
                ];
            })->values();

        return view('division.reports.census', compact(
            'division', 'populations', 'weeklyActive',
            'comments', 'censuses', 'weeklyTsActive'
        ));
    }
}
