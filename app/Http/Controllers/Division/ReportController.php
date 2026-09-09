<?php

namespace App\Http\Controllers\Division;

use App\Enums\ActivityType;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Division;
use App\Models\Member;
use App\Models\RankAction;
use App\Models\User;
use App\Repositories\DivisionRepository;
use App\Repositories\MemberRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

#[Middleware('auth')]
class ReportController extends Controller
{
    public function __construct(private DivisionRepository $division) {}

    public function retentionReport(Division $division): Response
    {
        [$start, $end, $range] = $this->parseDateRange(
            now()->subMonthsNoOverflow(6)->startOfMonth(),
            now()->endOfMonth(),
        );

        $activityCounts = Activity::query()
            ->where('name', ActivityType::RECRUITED)
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
                    'member'   => $user->member,
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

        $allMonths    = collect();
        $current      = Carbon::parse($range['start'])->startOfMonth();
        $endMonth     = Carbon::parse($range['end'])->startOfMonth();
        $currentMonth = now()->startOfMonth();
        if ($endMonth->gt($currentMonth)) {
            $endMonth = $currentMonth;
        }
        while ($current->lte($endMonth)) {
            $allMonths->push([
                'bucket' => $current->format('Y-m'),
                'date'   => $current->format('M y'),
            ]);
            $current->addMonth();
        }

        $recruitsKeyed   = $recruitsRaw->keyBy('bucket');
        $removalsKeyed   = $removalsRaw->keyBy('bucket');
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

        $netChange     = $totalRecruitCount - $totalRemovals;
        $retentionRate = $totalRecruitCount > 0
            ? round((($totalRecruitCount - $totalRemovals) / $totalRecruitCount) * 100, 1)
            : 0;

        $stats = [
            'recruits'      => $totalRecruitCount,
            'removals'      => $totalRemovals,
            'netChange'     => $netChange,
            'retentionRate' => $retentionRate,
        ];

        return Inertia::render('division/reports/retention', [
            'division'          => ['name' => $division->name, 'slug' => $division->slug],
            'range'             => $range,
            'stats'             => $stats,
            'totalRecruitCount' => $totalRecruitCount,
            'series'            => $recruits->map(fn ($row, $i) => [
                'month'    => $row[0],
                'recruits' => $row[1],
                'removals' => $removals[$i][1] ?? 0,
            ])->values(),
            'topRecruiters' => $members->map(fn ($item) => [
                'rankName' => $item['member']->present()->rankName,
                'recruits' => $item['recruits'],
                'url'      => route('member', $item['member']->getUrlParams()),
            ])->values(),
        ]);
    }

    public function voiceReport(Division $division): Response
    {
        $discordIssues = $division->members()
            ->misconfiguredDiscord()
            ->with('platoon')
            ->orderBy('last_voice_status')
            ->orderBy('name')
            ->get();

        $groupedByStatus = $discordIssues->groupBy(fn ($m) => $m->last_voice_status->value);

        $stats = [
            'total'           => $discordIssues->count(),
            'disconnected'    => $groupedByStatus->get('disconnected')?->count() ?? 0,
            'neverConnected'  => $groupedByStatus->get('never_connected')?->count() ?? 0,
            'neverConfigured' => $groupedByStatus->get('never_configured')?->count() ?? 0,
        ];

        return Inertia::render('division/reports/voice', [
            'division' => ['name' => $division->name, 'slug' => $division->slug, 'platoonLabel' => $division->locality('platoon')],
            'stats'    => $stats,
            'members'  => $discordIssues->map(fn (Member $member) => [
                'rankName'    => $member->present()->rankName,
                'status'      => $member->last_voice_status->value,
                'statusLabel' => $member->last_voice_status->getLabel(),
                'platoon'     => $member->platoon?->name,
                'discord'     => $member->discord,
                'lastActive'  => $member->present()->lastActive('last_voice_activity'),
                'url'         => route('member', $member->getUrlParams()),
                'forumUrl'    => doForumFunction([$member->clan_id], 'forumProfile'),
            ])->values(),
        ]);
    }

    public function censusReport(Division $division): Response
    {
        $censuses = $division->census
            ->sortByDesc('created_at')
            ->unique(fn ($c) => $c->created_at->toDateString())
            ->take(52)
            ->values();

        $latest   = $censuses->first();
        $previous = $censuses->skip(1)->first();

        $stats = [
            'population'   => $latest?->count ?? 0,
            'voicePercent' => $latest && $latest->count > 0
                ? round($latest->weekly_voice_count / $latest->count * 100, 1)
                : 0,
            'popChange'   => $latest && $previous ? $latest->count - $previous->count : 0,
            'voiceChange' => 0,
            'avgVoice'    => 0,
        ];

        if ($previous && $previous->count > 0 && $latest) {
            $prevVoice            = round($previous->weekly_voice_count / $previous->count * 100, 1);
            $stats['voiceChange'] = round($stats['voicePercent'] - $prevVoice, 1);
        }

        $recentWithPop = $censuses->take(4)->filter(fn ($c) => $c->count > 0);
        if ($recentWithPop->count() > 0) {
            $stats['avgVoice'] = round($recentWithPop->avg(fn ($c) => $c->weekly_voice_count / $c->count * 100), 1);
        }

        $peakCensus = $division->census
            ->unique(fn ($c) => $c->created_at->toDateString())
            ->sortByDesc('count')
            ->first();

        $stats['peakCount'] = $peakCensus?->count ?? 0;
        $stats['peakDate']  = $peakCensus?->created_at?->format('M j, Y');

        $ordered = $censuses->reverse()->values();

        return Inertia::render('division/reports/census', [
            'division' => ['name' => $division->name, 'slug' => $division->slug],
            'stats'    => $stats,
            'series'   => $ordered->map(fn ($census) => [
                'date'       => $census->created_at->format('M j'),
                'population' => $census->count,
                'voice'      => $census->weekly_voice_count,
            ])->values(),
            'weeks' => $censuses->map(function ($census, $index) use ($censuses) {
                $prev         = $censuses->skip($index + 1)->first();
                $voicePercent = $census->count > 0
                    ? round($census->weekly_voice_count / $census->count * 100, 1)
                    : 0;

                return [
                    'date'         => $census->created_at->format('M j, Y'),
                    'population'   => $census->count,
                    'change'       => $prev ? $census->count - $prev->count : 0,
                    'voicePercent' => $voicePercent,
                    'voiceCount'   => $census->weekly_voice_count,
                ];
            })->values(),
        ]);
    }

    public function promotionsReport(
        Request $request,
        MemberRepository $repository,
        Division $division,
        ?int $month = null,
        ?int $year = null
    ): Response {
        if ($period = $request->query('period')) {
            [$year, $month] = explode('-', $period);
            $year           = (int) $year;
            $month          = (int) $month;
        } else {
            $month = $month ? (int) $month : null;
            $year  = $year ? (int) $year : null;
        }

        $promotionPeriods = $this->promotionPeriodsFromActions($division);

        if ((! $month || ! $year) && $promotionPeriods->isNotEmpty()) {
            $firstPeriod = $promotionPeriods->first();
            $year        = $firstPeriod['year'];
            $month       = $firstPeriod['month'];
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

        $selectedKey = $year && $month ? sprintf('%04d-%02d', $year, $month) : null;

        $groups = $promotions
            ->groupBy(fn ($p) => $p->rank?->value ?? 0)
            ->sortKeysDesc()
            ->map(fn ($group) => [
                'rankName' => $group->first()->rank?->getLabel() ?? 'Unknown',
                'members'  => $group->map(fn ($action) => [
                    'name' => $action->member?->name ?? 'Unknown',
                    'url'  => $action->member ? route('member', $action->member->getUrlParams()) : null,
                    'date' => $action->approved_at?->format('M j, Y'),
                ])->values(),
            ])
            ->values();

        return Inertia::render('division/reports/promotions', [
            'division'    => ['name' => $division->name, 'slug' => $division->slug],
            'periods'     => $promotionPeriods,
            'selectedKey' => $selectedKey,
            'periodLabel' => $periodLabel,
            'chart'       => $ranks->map(fn ($rank, $i) => ['rank' => $rank, 'count' => $counts[$i] ?? 0])->values(),
            'groups'      => $groups,
            'total'       => $promotions->count(),
            'bbCode'      => $this->promotionsBbCode($promotions),
        ]);
    }

    private function promotionsBbCode(Collection $promotions): string
    {
        return $promotions
            ->groupBy(fn ($p) => $p->rank?->name ?? 'Unspecified')
            ->map(function ($actions, $rank) {
                $names = $actions->map(fn ($a) => '[*]' . $a->member?->name)->implode("\n");

                return '[b]' . strtoupper($rank) . "[/b]\n[list]\n" . $names . "\n[/list]";
            })
            ->implode("\n\n");
    }

    private function promotionPeriodsFromActions(Division $division)
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

        return $rows->map(fn ($r) => [
            'year'  => $r['y'],
            'month' => $r['m'],
            'label' => Carbon::createFromDate($r['y'], $r['m'], 1)->format('F Y'),
            'key'   => sprintf('%04d-%02d', $r['y'], $r['m']),
        ])->values();
    }

    private function getDivisionPromotions(Division $division, ?int $month, ?int $year): Collection
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

    public function transferReport(Division $division): Response
    {
        [$start, $end, $range] = $this->parseDateRange();

        $sources = $this->applyDateRange(
            DB::table('activities as a')
                ->leftJoin('divisions as d', 'd.id', '=', 'a.division_id')
                ->join('members as m', 'm.id', '=', 'a.subject_id')
                ->where('a.subject_type', Member::class)
                ->where('a.name', ActivityType::TRANSFERRED->value)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.to_division')) = ?", [$division->name])
                ->where('m.division_id', $division->id)
                ->whereNull('m.deleted_at')
                ->select('a.division_id', 'd.name as division_name', DB::raw('COUNT(*) as count'))
                ->groupBy('a.division_id', 'd.name')
                ->orderByDesc('count'),
            $start,
            $end,
        )->get();

        $recruitedCount = $this->applyDateRange(
            DB::table('activities as a')
                ->join('members as m', 'm.id', '=', 'a.subject_id')
                ->where('a.subject_type', Member::class)
                ->where('a.name', ActivityType::RECRUITED->value)
                ->where('a.division_id', $division->id)
                ->where('m.division_id', $division->id)
                ->whereNull('m.deleted_at'),
            $start,
            $end,
        )->count();

        $total = $sources->sum('count') + $recruitedCount;

        $sources = $sources->map(fn ($row) => [
            'name'        => $row->division_name ?? 'Unknown',
            'count'       => (int) $row->count,
            'percentage'  => $total > 0 ? round($row->count / $total * 100, 1) : 0,
            'is_original' => false,
        ]);

        if ($recruitedCount > 0) {
            $sources->push([
                'name'        => 'Started here',
                'count'       => $recruitedCount,
                'percentage'  => $total > 0 ? round($recruitedCount / $total * 100, 1) : 0,
                'is_original' => true,
            ]);
        }

        $stats = [
            'total'         => $total,
            'sourceCount'   => $sources->where('is_original', false)->count(),
            'transferredIn' => $sources->where('is_original', false)->sum('count'),
            'startedHere'   => $sources->where('is_original', true)->sum('count'),
        ];

        return Inertia::render('division/reports/transfers', [
            'division' => ['name' => $division->name, 'slug' => $division->slug],
            'range'    => $range,
            'stats'    => $stats,
            'sources'  => $sources->values(),
        ]);
    }

    private function parseDateRange(?Carbon $defaultStart = null, ?Carbon $defaultEnd = null): array
    {
        $start = request()->filled('start')
            ? Carbon::parse(request('start'))->startOfDay()
            : $defaultStart;

        $end = request()->filled('end')
            ? Carbon::parse(request('end'))->endOfDay()
            : $defaultEnd;

        $range = [
            'start' => request('start') ?? $defaultStart?->format('Y-m-d') ?? '',
            'end'   => request('end') ?? $defaultEnd?->format('Y-m-d') ?? '',
        ];

        return [$start, $end, $range];
    }

    private function applyDateRange($query, ?Carbon $start, ?Carbon $end)
    {
        if ($start) {
            $query->where('a.created_at', '>=', $start);
        }

        if ($end) {
            $query->where('a.created_at', '<=', $end);
        }

        return $query;
    }

    private function resolveMonthWindow(?int $month, ?int $year): array
    {
        if ($month && $year) {
            try {
                $start = ctype_digit((string) $month)
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
