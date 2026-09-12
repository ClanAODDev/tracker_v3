<?php

namespace App\Data;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Division;
use App\Models\User;
use App\Repositories\DivisionRepository;
use App\Support\DateRangeRequest;
use App\Support\MonthlySeriesBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DivisionRetentionReportData
{
    private array $range;

    private int $totalRecruitCount;

    private int $totalRemovals;

    private array $stats;

    private Collection $recruits;

    private Collection $removals;

    private Collection $topRecruiters;

    public function __construct(private Division $division, DivisionRepository $repository)
    {
        [$start, $end, $this->range] = DateRangeRequest::parse(
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

        $this->topRecruiters = $activityCounts
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

        $this->totalRecruitCount = (int) $activityCounts->sum('recruits');

        $recruitsRaw = $repository->recruitsLast6Months($division->id, $this->range['start'], $this->range['end'] ?? null);
        $removalsRaw = $repository->removalsLast6Months($division->id, $this->range['start'], $this->range['end'] ?? null);

        $this->totalRemovals = $removalsRaw->sum('removals');

        $months         = MonthlySeriesBuilder::months(Carbon::parse($this->range['start']), Carbon::parse($this->range['end']));
        $this->recruits = MonthlySeriesBuilder::fill($months, $recruitsRaw, 'recruits');
        $this->removals = MonthlySeriesBuilder::fill($months, $removalsRaw, 'removals');

        $retentionRate = $this->totalRecruitCount > 0
            ? round((($this->totalRecruitCount - $this->totalRemovals) / $this->totalRecruitCount) * 100, 1)
            : 0;

        $this->stats = [
            'recruits'      => $this->totalRecruitCount,
            'removals'      => $this->totalRemovals,
            'netChange'     => $this->totalRecruitCount - $this->totalRemovals,
            'retentionRate' => $retentionRate,
        ];
    }

    public static function for(Division $division, DivisionRepository $repository): self
    {
        return new self($division, $repository);
    }

    public function toArray(): array
    {
        return [
            'division'          => ['name' => $this->division->name, 'slug' => $this->division->slug],
            'range'             => $this->range,
            'stats'             => $this->stats,
            'totalRecruitCount' => $this->totalRecruitCount,
            'series'            => $this->recruits->map(fn ($row, $i) => [
                'month'    => $row[0],
                'recruits' => $row[1],
                'removals' => $this->removals[$i][1] ?? 0,
            ])->values(),
            'topRecruiters' => $this->topRecruiters->map(fn ($item) => [
                'rankName' => $item['member']->present()->rankName,
                'recruits' => $item['recruits'],
                'url'      => route('member', $item['member']->getUrlParams()),
            ])->values(),
        ];
    }
}
