<?php

namespace App\Http\Controllers\Division;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Division;
use App\Models\RankAction;
use App\Models\User;
use App\Repositories\DivisionRepository;
use App\Repositories\MemberRepository;
use DB;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Throwable;

class ReportController extends Controller
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

        $activityCounts = Activity::query()
            ->where('name', 'recruited_member')
            ->where('division_id', $division->id)
            ->whereBetween('created_at', [$start, $end])
            ->select('user_id', DB::raw('COUNT(*) as recruits'))
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

        $recruitsRaw = $this->division
            ->recruitsLast6Months($division->id, $range['start'], $range['end'] ?? null);

        $removalsRaw = $this->division
            ->removalsLast6Months($division->id, $range['start'], $range['end'] ?? null);

        $populationRaw = $this->division
            ->populationLast6Months($division->id, $range['start'], $range['end'] ?? null);

        $totalRemovals = $removalsRaw->sum('removals');

        $allMonths = collect();
        $current = Carbon::parse($range['start'])->startOfMonth();
        $endMonth = Carbon::parse($range['end'])->startOfMonth();
        $currentMonth = now()->startOfMonth();
        if ($endMonth->gt($currentMonth)) {
            $endMonth = $currentMonth;
        }
        while ($current->lte($endMonth)) {
            $allMonths->push([
                'bucket' => $current->format('Y-m'),
                'date' => $current->format('M y'),
            ]);
            $current->addMonth();
        }

        $recruitsKeyed = $recruitsRaw->keyBy('bucket');
        $removalsKeyed = $removalsRaw->keyBy('bucket');
        $populationKeyed = $populationRaw->keyBy('bucket');

        $recruits = $allMonths->map(fn ($m) => [
            $m['date'],
            $recruitsKeyed->has($m['bucket']) ? $recruitsKeyed->get($m['bucket'])->recruits : 0,
        ]);

        $removals = $allMonths->map(fn ($m) => [
            $m['date'],
            $removalsKeyed->has($m['bucket']) ? $removalsKeyed->get($m['bucket'])->removals : 0,
        ]);

        $population = $allMonths->map(fn ($m) => [
            $m['date'],
            $populationKeyed->has($m['bucket']) ? $populationKeyed->get($m['bucket'])->count : 0,
        ]);

        $netChange = $totalRecruitCount - $totalRemovals;
        $retentionRate = $totalRecruitCount > 0
            ? round((($totalRecruitCount - $totalRemovals) / $totalRecruitCount) * 100, 1)
            : 0;

        $stats = [
            'recruits' => $totalRecruitCount,
            'removals' => $totalRemovals,
            'netChange' => $netChange,
            'retentionRate' => $retentionRate,
        ];

        return view('division.reports.retention-report', compact(
            'division',
            'members',
            'totalRecruitCount',
            'population',
            'range',
            'recruits',
            'removals',
            'stats'
        ));
    }

    public function voiceReport(Division $division)
    {
        $discordIssues = $division->members()
            ->misconfiguredDiscord()
            ->with('platoon')
            ->orderBy('last_voice_status')
            ->orderBy('name')
            ->get();

        $groupedByStatus = $discordIssues->groupBy(fn ($m) => $m->last_voice_status->value);

        $stats = [
            'total' => $discordIssues->count(),
            'disconnected' => $groupedByStatus->get('disconnected')?->count() ?? 0,
            'neverConnected' => $groupedByStatus->get('never_connected')?->count() ?? 0,
            'neverConfigured' => $groupedByStatus->get('never_configured')?->count() ?? 0,
        ];

        return view('division.reports.voice-report', compact(
            'division',
            'discordIssues',
            'groupedByStatus',
            'stats',
        ));
    }

    /**
     * @return Factory|View
     */
    public function censusReport(Division $division)
    {
        $censuses = $division->census->sortByDesc('created_at')->take(52)->values();

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

        $latest = $censuses->first();
        $previous = $censuses->skip(1)->first();

        $stats = [
            'population' => $latest?->count ?? 0,
            'voicePercent' => $latest && $latest->count > 0
                ? round($latest->weekly_voice_count / $latest->count * 100, 1)
                : 0,
            'popChange' => $latest && $previous ? $latest->count - $previous->count : 0,
            'voiceChange' => 0,
            'avgVoice' => 0,
        ];

        if ($previous && $previous->count > 0 && $latest) {
            $prevVoice = round($previous->weekly_voice_count / $previous->count * 100, 1);
            $stats['voiceChange'] = round($stats['voicePercent'] - $prevVoice, 1);
        }

        $recentWithPop = $censuses->take(4)->filter(fn ($c) => $c->count > 0);
        if ($recentWithPop->count() > 0) {
            $stats['avgVoice'] = round($recentWithPop->avg(fn ($c) => $c->weekly_voice_count / $c->count * 100), 1);
        }

        return view('division.reports.census', compact(
            'division',
            'populations',
            'weeklyActive',
            'comments',
            'censuses',
            'weeklyTsActive',
            'weeklyDiscordActive',
            'stats',
        ));
    }

    public function promotionsReport(
        Request $request,
        MemberRepository $repository,
        $division,
        $month = null,
        $year = null
    ) {
        if ($period = $request->query('period')) {
            [$year, $month] = explode('-', $period);
            $year = (int) $year;
            $month = (int) $month;
        } else {
            $month = $month ? (int) $month : null;
            $year = $year ? (int) $year : null;
        }

        $promotionPeriods = $this->promotionPeriodsFromActions($division);

        if ((! $month || ! $year) && $promotionPeriods->isNotEmpty()) {
            $firstPeriod = $promotionPeriods->first();
            $year = $firstPeriod['year'];
            $month = $firstPeriod['month'];
        }

        try {
            $promotions = $this->getDivisionPromotions($division, $month, $year);
        } catch (Throwable $e) {
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

        $periodLabel = $year && $month
            ? Carbon::createFromDate((int) $year, (int) $month, 1)->format('F Y')
            : now()->format('F Y');

        return view('division.reports.promotions', [
            'promotions' => $promotions,
            'division' => $division,
            'promotionPeriods' => $promotionPeriods,
            'year' => $year,
            'month' => $month,
            'ranks' => $ranks,
            'counts' => $counts,
            'periodLabel' => $periodLabel,
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
            ->orderByDesc('rank')
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
            } catch (Throwable $e) {
                $start = now()->startOfMonth();
            }
        } else {
            $start = now()->startOfMonth();
        }

        $end = (clone $start)->endOfMonth();

        return [$start, $end];
    }
}
