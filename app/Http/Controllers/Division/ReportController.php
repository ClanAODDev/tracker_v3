<?php

namespace App\Http\Controllers\Division;

use App\Models\Division;
use App\Repositories\DivisionRepository;
use App\Repositories\MemberRepository;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class ReportController extends \App\Http\Controllers\Controller
{
    use IngameReports;

    public function __construct(DivisionRepository $division)
    {
        $this->division = $division;
        $this->middleware(['auth', 'activeDivision']);
    }

    /**
     * @return Factory|View
     */
    public function retentionReport(Division $division)
    {
        $range = ['start' => request('start') ?? now()->subMonths(6)->startOfMonth()->format('Y-m-d'), 'end' => request('end') ?? now()->endOfMonth()->format('Y-m-d')];
        $activity = collect(\App\Models\Activity::whereName('recruited_member')->whereDivisionId($division->id)->whereBetween('created_at', [$range['start'], $range['end']])->with('user.member')->with('user.member.rank')->get())->groupBy('user_id');
        $members = $activity->map(function ($item) {
            if ($item->first()->user) {
                return ['recruits' => \count($item), 'member' => $item->first()->user->member];
            }
        })->sortByDesc('recruits');
        $totalRecruitCount = $members->map(function ($item) {
            if ($item !== null) {
                return $item['recruits'];
            }
        })->sum();
        $recruits = $this->division->recruitsLast6Months($division->id, $range['start'])->map(fn ($record) => [$record->date, $record->recruits]);
        $removals = $this->division->removalsLast6Months($division->id, $range['start'])->map(fn ($record) => [$record->date, $record->removals]);
        $population = $this->division->populationLast6Months($division->id, $range['start'])->map(fn ($record) => [$record->date, $record->count]);

        return view('division.reports.retention-report', compact('division', 'members', 'totalRecruitCount', 'population', 'range', 'recruits', 'removals'));
    }

    /**
     * @param  null|mixed  $customAttr
     * @return Factory|View
     */
    public function ingameReport(Division $division, $customAttr = null)
    {
        $method = \Illuminate\Support\Str::camel($division->name);
        if (method_exists($this, $method)) {
            $data = $this->{$method}($customAttr);
        } else {
            $data = [];
        }

        return view('division.reports.ingame-report', compact('division', 'data'));
    }

    /**
     * @param  null  $month
     * @param  null  $year
     * @return Factory|View
     */
    public function promotionsReport(MemberRepository $repository, $division, $month = null, $year = null)
    {
        try {
            $members = $this->getMemberPromotions($division, $month, $year);
        } catch (\Exception $exception) {
            $members = collect([]);
        }
        $ranks = $members->pluck('rank.abbreviation')->unique();
        $counts = $members->groupBy('rank_id')->each(function ($rank) {
            $rank->count = \count($rank);
        })->pluck('count');
        $promotionPeriods = $repository->promotionPeriods();

        return view('division.reports.promotions', compact('members', 'division', 'promotionPeriods', 'year', 'month', 'ranks', 'counts'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View
     */
    public function voiceReport(Division $division)
    {
        $tsIssues = $division->mismatchedTSMembers;

        $discordIssues = $division->membersOfDiscordState([
            'never_connected',
            'never_configured',
            'disconnected',
        ])->get();

        return view('division.reports.voice-report', compact(
            'division',
            'discordIssues',
            'tsIssues',
        ));
    }

    /**
     * @return Factory|View
     */
    public function censusReport(Division $division)
    {
        $censuses = $division->census->sortByDesc('created_at')->take(52);
        $populations = $censuses->values()->map(fn ($census, $key) => [$census->javascriptTimestamp, $census->count]);
        $weeklyActive = $censuses->values()->map(fn ($census, $key) => [$census->javascriptTimestamp, $census->weekly_active_count]);
        $weeklyTsActive = $censuses->values()->map(fn ($census, $key) => [$census->javascriptTimestamp, $census->weekly_ts_count]);
        $comments = $censuses->values()->filter(fn ($census) => $census->notes)->map(fn ($census, $key) => ['x' => $key, 'y' => $censuses->values()->pluck('count'), 'contents' => $census->notes])->values();

        return view('division.reports.census', compact('division', 'populations', 'weeklyActive', 'comments', 'censuses', 'weeklyTsActive'));
    }

    /**
     * @return mixed
     */
    private function getMemberPromotions($division, $month, $year)
    {
        $dates = $month && $year ? [\Carbon\Carbon::parse($month . " {$year}")->startOfMonth(), \Carbon\Carbon::parse($month . " {$year}")->endOfMonth()] : [\Carbon\Carbon::now()->startOfMonth(), \Carbon\Carbon::now()->endOfMonth()];

        return $division->members()->with('rank')->whereBetween('last_promoted_at', $dates)->orderByDesc('rank_id')->get();
    }
}
