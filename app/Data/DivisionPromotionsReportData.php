<?php

namespace App\Data;

use App\Models\Division;
use App\Models\RankAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

class DivisionPromotionsReportData
{
    private Collection $promotionPeriods;

    private ?string $selectedKey;

    private string $periodLabel;

    private Collection $promotions;

    public function __construct(private Division $division, ?int $month, ?int $year)
    {
        $this->promotionPeriods = $this->promotionPeriodsFromActions($division);

        if ((! $month || ! $year) && $this->promotionPeriods->isNotEmpty()) {
            $firstPeriod = $this->promotionPeriods->first();
            $year        = $firstPeriod['year'];
            $month       = $firstPeriod['month'];
        }

        try {
            $this->promotions = $this->getDivisionPromotions($division, $month, $year);
        } catch (Throwable $e) {
            $this->promotions = collect();
        }

        $this->periodLabel = $year && $month
            ? Carbon::createFromDate((int) $year, (int) $month, 1)->format('F Y')
            : now()->format('F Y');

        $this->selectedKey = $year && $month ? sprintf('%04d-%02d', $year, $month) : null;
    }

    public static function for(Division $division, ?int $month, ?int $year): self
    {
        return new self($division, $month, $year);
    }

    public function toArray(): array
    {
        $promotions = $this->promotions;

        $ranks = $promotions
            ->pluck('rank')
            ->filter()
            ->unique()
            ->map(fn ($r) => method_exists($r, 'abbreviation') ? $r->abbreviation()
                : (method_exists($r, 'getAbbreviation') ? $r->getAbbreviation()
                    : ($r->name ?? (string) $r)))
            ->values();

        $counts = $promotions->groupBy('rank')->map->count()->values();

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

        return [
            'division'    => ['name' => $this->division->name, 'slug' => $this->division->slug],
            'periods'     => $this->promotionPeriods,
            'selectedKey' => $this->selectedKey,
            'periodLabel' => $this->periodLabel,
            'chart'       => $ranks->map(fn ($rank, $i) => ['rank' => $rank, 'count' => $counts[$i] ?? 0])->values(),
            'groups'      => $groups,
            'total'       => $promotions->count(),
            'bbCode'      => $this->promotionsBbCode($promotions),
        ];
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

    private function promotionPeriodsFromActions(Division $division): Collection
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
