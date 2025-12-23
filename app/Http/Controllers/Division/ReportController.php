<?php

namespace App\Http\Controllers\Division;

use App\Models\Division;
use App\Models\RankAction;
use App\Models\User;
use App\Repositories\DivisionRepository;
use App\Repositories\MemberRepository;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends \App\Http\Controllers\Controller
{
    public function __construct(DivisionRepository $division)
    {
        $this->division = $division;
        $this->middleware('auth');
    }

    /**
     * @return Factory|View
     */
    public function retentionReport(Division $division)
    {
        $defaultStart = now()->subMonthsNoOverflow(6)->startOfMonth();
        $defaultEnd = now()->endOfMonth();

        $start = request()->filled('start')
            ? Carbon::parse(request('start'))->startOfDay()
            : $defaultStart;

        $end = request()->filled('end')
            ? Carbon::parse(request('end'))->endOfDay()
            : $defaultEnd;

        $range = [
            'start' => request('start') ?? now()->subMonths(6)->startOfMonth()->format('Y-m-d'),
            'end' => request('end') ?? now()->endOfMonth()->format('Y-m-d'),
        ];

        $activityCounts = \App\Models\Activity::query()
            ->where('name', 'recruited_member')
            ->where('division_id', $division->id)
            ->whereBetween('created_at', [$start, $end])
            ->select('user_id', \DB::raw('COUNT(*) as recruits'))
            ->groupBy('user_id')
            ->get();

        $users = User::query()
            ->with('member')
            ->whereIn('id', $activityCounts->pluck('user_id'))
            ->get()
            ->keyBy('id');

        $members = $activityCounts
            ->map(function ($row) use ($users) {
                $user = $users->get($row->user_id);
                if (! $user || ! $user->member) {
                    return null;
                }

                return [
                    'recruits' => (int) $row->recruits,
                    'member' => $user->member,
                ];
            })
            ->filter()
            ->sortByDesc('recruits')
            ->values();

        $totalRecruitCount = (int) $activityCounts->sum('recruits');

        $recruits = $this->division
            ->recruitsLast6Months($division->id, $range['start'], $range['end'] ?? null)
            ->map(fn ($r) => [$r->date, $r->recruits]);

        $removals = $this->division
            ->removalsLast6Months($division->id, $range['start'], $range['end'] ?? null)
            ->map(fn ($r) => [$r->date, $r->removals]);

        $population = $this->division
            ->populationLast6Months($division->id, $range['start'], $range['end'] ?? null)
            ->map(fn ($r) => [$r->date, $r->count]);

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

    public function voiceReport(Division $division)
    {
        $discordIssues = $division->members()->misconfiguredDiscord()->get();

        return view('division.reports.voice-report', compact(
            'division',
            'discordIssues',
        ));
    }

    /**
     * @return Factory|View
     */
    public function censusReport(Division $division)
    {
        $censuses = $division->census->sortByDesc('created_at')->take(52);

        $populations = $censuses->values()->map(fn ($census, $key) => [
            $census->javascriptTimestamp, $census->count,
        ]);

        $weeklyActive = $censuses->values()->map(fn ($census, $key) => [
            $census->javascriptTimestamp, $census->weekly_active_count,
        ]);

        $weeklyTsActive = $censuses->values()->map(fn ($census, $key) => [
            $census->javascriptTimestamp, $census->weekly_ts_count,
        ]);

        $weeklyDiscordActive = $censuses->values()->map(fn ($census, $key) => [
            $census->javascriptTimestamp, $census->weekly_voice_count,
        ]);

        $comments = $censuses->values()->filter(fn ($census) => $census->notes)->map(fn ($census, $key) => [
            'x' => $key, 'y' => $censuses->values()->pluck('count'), 'contents' => $census->notes,
        ])->values();

        return view('division.reports.census', compact(
            'division',
            'populations',
            'weeklyActive',
            'comments',
            'censuses',
            'weeklyTsActive',
            'weeklyDiscordActive',
        ));
    }

    public function promotionsReport(
        Request $request,
        MemberRepository $repository,
        $division,
        $month = null,
        $year = null
    ) {
        $month = $month ?? $request->query('month');
        $year = $year ?? $request->query('year');

        try {
            $promotions = $this->getDivisionPromotions($division, $month, $year);
        } catch (\Throwable $e) {
            $promotions = collect();
        }

        $ranks = $promotions
            ->pluck('rank')
            ->filter()
            ->unique()
            ->map(fn ($r) => method_exists($r, 'abbreviation') ? $r->abbreviation()
                : (method_exists($r, 'getAbbreviation') ? $r->getAbbreviation()
                    : ($r->name ?? (string) $r)))
            ->values();

        $counts = $promotions->groupBy('rank')->map->count()->values();

        $promotionPeriods = $this->promotionPeriodsFromActions($division);

        return view('division.reports.promotions', [
            'promotions' => $promotions,
            'division' => $division,
            'promotionPeriods' => $promotionPeriods,
            'year' => $year,
            'month' => $month,
            'ranks' => $ranks,
            'counts' => $counts,
        ]);
    }

    private function promotionPeriodsFromActions($division)
    {
        $base = RankAction::query()
            ->whereNotNull('approved_at')
            ->whereHas('member', fn ($q) => $q->where('division_id', $division->id));

        $rows = $base->get(['approved_at'])
            ->map(fn ($r) => [
                'y' => (int) $r->approved_at->format('Y'),
                'm' => (int) $r->approved_at->format('n'),
            ])
            ->unique(fn ($r) => $r['y'] . '-' . $r['m'])
            ->sortByDesc(fn ($r) => sprintf('%04d-%02d', $r['y'], $r['m']))
            ->values();

        return $rows->map(function ($r) {
            $y = is_array($r) ? $r['y'] : (int) $r->y;
            $m = is_array($r) ? $r['m'] : (int) $r->m;

            return [
                'year' => $y,
                'month' => $m,
                'label' => \Carbon::createFromDate($y, $m, 1)->format('F Y'),
                'key' => sprintf('%04d-%02d', $y, $m),
            ];
        })->values();
    }

    private function getDivisionPromotions($division, $month, $year): Collection
    {
        [$start, $end] = $this->resolveMonthWindow($month, $year);

        return RankAction::query()
            ->with('member')
            ->whereNotNull('approved_at')
            ->whereBetween('approved_at', [$start, $end])
            ->whereHas('member', fn ($q) => $q->where('division_id', $division->id))
            ->orderByDesc('approved_at')
            ->get();
    }

    private function resolveMonthWindow($month, $year): array
    {
        if ($month && $year) {
            try {
                $start = \ctype_digit((string) $month)
                    ? Carbon::createFromDate((int) $year, (int) $month, 1)->startOfMonth()
                    : Carbon::parse("first day of {$month} {$year}")->startOfDay()->startOfMonth();
            } catch (\Throwable $e) {
                $start = now()->startOfMonth();
            }
        } else {
            $start = now()->startOfMonth();
        }

        $end = (clone $start)->endOfMonth();

        return [$start, $end];
    }
}
