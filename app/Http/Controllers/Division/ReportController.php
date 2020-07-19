<?php

namespace App\Http\Controllers\Division;

use App\Activity;
use App\Division;
use App\Http\Controllers\Controller;
use App\Repositories\DivisionRepository;
use App\Repositories\MemberRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
    use IngameReports;

    public function __construct(DivisionRepository $division)
    {
        $this->division = $division;

        $this->middleware(['auth', 'activeDivision']);
    }

    /**
     * @param  Division  $division
     * @return Factory|View
     */
    public function retentionReport(Division $division)
    {
        $range = [
            'start' => request('start') ?? now()->subMonths(6)->startOfMonth()
                    ->format('Y-m-d'),
            'end' => request('end') ?? now()->endOfMonth()
                    ->format('Y-m-d')
        ];

        $activity = collect(Activity::whereName('recruited_member')
            ->whereDivisionId($division->id)
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->with('user.member')
            ->with('user.member.rank')
            ->get())
            ->groupBy('user_id');

        $members = $activity->map(function ($item) {
            if ($item->first()->user) {
                return [
                    'recruits' => count($item),
                    'member' => $item->first()->user->member
                ];
            }
        })->sortByDesc('recruits');

        $totalRecruitCount = $members->map(function ($item) {
            if (!is_null($item)) {
                return $item['recruits'];
            }
        })->sum();

        $recruits = $this->division->recruitsLast6Months($division->id, $range['start'])->map(function ($record) {
            return [$record->date, $record->recruits];
        });

        $removals = $this->division->removalsLast6Months($division->id, $range['start'])->map(function ($record) {
            return [$record->date, $record->removals];
        });

        $population = $this->division->populationLast6Months($division->id, $range['start'])->map(function ($record) {
            return [$record->date, $record->count];
        });

        return view('division.reports.retention-report', compact(
            'division',
            'members',
            'totalRecruitCount',
            'population',
            'range',
            'recruits',
            'removals'
        ));
    }

    /**
     * @param  Division  $division
     * @return Factory|View
     */
    public function ingameReport(Division $division, $customAttr = null)
    {
        $method = Str::camel($division->name);

        if (method_exists($this, $method)) {
            $data = $this->$method($customAttr);
        } else {
            $data = [];
        }

        return view('division.reports.ingame-report', compact('division', 'data'));
    }

    /**
     * @param  MemberRepository  $repository
     * @param $division
     * @param  null  $month
     * @param  null  $year
     * @return Factory|View
     */
    public function promotionsReport(MemberRepository $repository, $division, $month = null, $year = null)
    {
        try {
            $members = $this->getMemberPromotions($division, $month, $year);
        } catch (Exception $exception) {
            $members = collect([]);
        }

        $ranks = $members->pluck('rank.abbreviation')->unique();
        $counts = $members->groupBy('rank_id')->each(function ($rank) {
            $rank->count = count($rank);
        })->pluck('count');

        $promotionPeriods = $repository->promotionPeriods();

        return view('division.reports.promotions', compact(
            'members',
            'division',
            'promotionPeriods',
            'year',
            'month',
            'ranks',
            'counts'
        ));
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
            ->whereBetween('last_promoted_at', $dates)
            ->orderByDesc('rank_id')->get();

        return $members;
    }

    /**
     * @param  Division  $division
     * @return Factory|View
     */
    public function tsReport(Division $division)
    {
        $issues = $division->mismatchedTSMembers;

        return view('division.reports.ts-report', compact('division', 'issues'));
    }

    /**
     * @param  Division  $division
     * @return Factory|View
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
            'division',
            'populations',
            'weeklyActive',
            'comments',
            'censuses',
            'weeklyTsActive'
        ));
    }
}
