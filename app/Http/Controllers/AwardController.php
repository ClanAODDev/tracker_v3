<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\MemberAward;
use App\Rules\UniqueAwardForMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AwardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $divisionSlug = request('division');

        $query = Award::active()
            ->orderBy('display_order')
            ->withCount('recipients')
            ->with('division');

        if ($divisionSlug) {
            $query->whereHas('division', fn (Builder $q) => $q->where('slug', $divisionSlug));
        }

        $allAwards = $query->get();

        $awards = $allAwards->filter(function ($award) {
            if ($award->division_id === null) {
                return true;
            }
            if ($award->division?->active) {
                return true;
            }

            return $award->recipients_count > 0;
        });

        if ($divisionSlug && $awards->isEmpty()) {
            $this->showErrorToast('Selected division has no awards assigned. Showing all...');

            return redirect(route('awards.index'));
        }

        $maxRecipients = $awards->max('recipients_count') ?: 1;
        $awards->each(function ($award) use ($maxRecipients) {
            $award->popularityPct = round($award->recipients_count / $maxRecipients * 100);
            $award->rarity = $award->getRarity();
        });

        $clanAwards = $awards->whereNull('division_id')->values();

        $activeAwards = $awards
            ->whereNotNull('division_id')
            ->filter(fn ($award) => $award->division?->active || $award->division?->slug === $divisionSlug)
            ->groupBy('division.name');

        $legacyAwards = $awards
            ->whereNotNull('division_id')
            ->filter(fn ($award) => ! $award->division?->active && $award->recipients_count > 0 && $award->division?->slug !== $divisionSlug)
            ->groupBy('division.name');

        $activeAndClanAwards = $awards->filter(fn ($a) => $a->division_id === null || $a->division?->active);
        $totals = (object) [
            'awards' => $awards->count(),
            'recipients' => $awards->sum('recipients_count'),
            'requestable' => $activeAndClanAwards->where('allow_request', true)->count(),
        ];

        $divisionsWithAwards = Award::active()
            ->whereNotNull('division_id')
            ->withCount('recipients')
            ->with('division:id,name,slug,active')
            ->get()
            ->filter(fn ($award) => $award->division?->active || $award->recipients_count > 0)
            ->pluck('division')
            ->unique('id')
            ->sortBy([['active', 'desc'], ['name', 'asc']])
            ->values();

        $tieredGroups = $this->buildTieredGroups();
        $tieredAwardIds = collect($tieredGroups)->flatten()->pluck('id')->toArray();

        $clanAwards = $clanAwards->reject(fn ($a) => in_array($a->id, $tieredAwardIds));

        return view('division.awards.index', compact(
            'awards',
            'clanAwards',
            'activeAwards',
            'legacyAwards',
            'totals',
            'divisionSlug',
            'divisionsWithAwards',
            'tieredGroups'
        ));
    }

    private function buildTieredGroups(): array
    {
        $awardsWithChains = Award::active()
            ->where(function ($q) {
                $q->whereNotNull('prerequisite_award_id')
                    ->orWhereHas('dependents');
            })
            ->withCount('recipients')
            ->orderBy('display_order')
            ->get();

        $processed = [];
        $groups = [];

        foreach ($awardsWithChains as $award) {
            if (in_array($award->id, $processed)) {
                continue;
            }

            $chain = collect([$award]);
            $current = $award->prerequisite;
            while ($current) {
                $chain->push($current);
                $current = $current->prerequisite;
            }

            $current = $award;
            while ($dependent = Award::where('prerequisite_award_id', $current->id)->first()) {
                $chain->push($dependent);
                $current = $dependent;
            }

            $chain = $chain->unique('id')->sortBy('display_order')->values();

            $chainIds = $chain->pluck('id')->toArray();
            $processed = array_merge($processed, $chainIds);

            $topTier = $chain->last();
            $recipientCount = MemberAward::whereIn('award_id', $chainIds)
                ->where('approved', true)
                ->whereHas('member', fn ($q) => $q->where('division_id', '>', 0))
                ->distinct('member_id')
                ->count('member_id');

            $baseTier = $chain->first();
            $groupName = $baseTier->tiered_group_name ?? $this->getTieredGroupName($chain);
            $division = $baseTier->division;
            $groups[] = [
                'name' => $groupName,
                'slug' => Str::slug($groupName),
                'description' => $baseTier->tiered_group_description ?? $this->getTieredGroupDescription($chain, $groupName),
                'tiers' => $chain,
                'topTier' => $topTier,
                'recipientCount' => $recipientCount,
                'division_id' => $division?->id,
                'division' => $division,
            ];
        }

        return $groups;
    }

    private function getTieredGroupName($chain): string
    {
        $names = $chain->pluck('name')->toArray();

        if (collect($names)->contains(fn ($n) => str_contains($n, 'Years of Service'))) {
            return 'AOD Tenure';
        }

        $commonWords = [];
        $firstWords = explode(' ', $names[0]);
        foreach ($firstWords as $word) {
            if (collect($names)->every(fn ($n) => str_contains($n, $word))) {
                $commonWords[] = $word;
            }
        }

        return ! empty($commonWords) ? implode(' ', $commonWords) : $chain->first()->name . ' Series';
    }

    private function getTieredGroupDescription($chain, string $groupName): string
    {
        $tierCount = $chain->count();
        $topTier = $chain->last();

        if (str_contains($groupName, 'Tenure')) {
            return 'Recognition for years of dedicated service to the Angels of Death clan. Each tier represents a milestone in your AOD journey.';
        }

        return "A {$tierCount}-tier progression culminating in {$topTier->name}. Earn each tier in sequence to complete the set.";
    }

    public function tiered(string $slug)
    {
        $tieredGroups = $this->buildTieredGroups();
        $group = collect($tieredGroups)->firstWhere('slug', $slug);

        if (! $group) {
            abort(404);
        }

        $tierIds = $group['tiers']->pluck('id')->toArray();
        $tiers = Award::whereIn('id', $tierIds)
            ->withCount('recipients')
            ->orderBy('display_order')
            ->get();

        $userMember = auth()->user()?->member;

        $userAwards = $userMember
            ? MemberAward::where('member_id', $userMember->clan_id)
                ->whereIn('award_id', $tierIds)
                ->where('approved', true)
                ->get()
                ->keyBy('award_id')
            : collect();

        $userAwardIds = $userAwards->pluck('award_id')->toArray();
        $earnedCount = count($userAwardIds);
        $totalTiers = $tiers->count();

        $nextTierId = null;
        foreach ($tiers as $tier) {
            if (! in_array($tier->id, $userAwardIds)) {
                $nextTierId = $tier->id;
                break;
            }
        }

        $stats = (object) [
            'totalRecipients' => $group['recipientCount'],
            'firstAwarded' => MemberAward::whereIn('award_id', $tierIds)
                ->where('approved', true)
                ->orderBy('created_at')
                ->first()?->created_at,
            'earnedCount' => $earnedCount,
            'totalTiers' => $totalTiers,
            'progressPct' => $totalTiers > 0 ? round(($earnedCount / $totalTiers) * 100) : 0,
        ];

        return view('division.awards.tiered', compact('group', 'tiers', 'userAwards', 'userAwardIds', 'nextTierId', 'stats'));
    }

    public function show(Award $award)
    {
        $award->load(['division']);
        $award->loadCount('recipients');

        $recipients = MemberAward::where('award_id', $award->id)
            ->where('approved', true)
            ->whereHas('member', fn ($q) => $q->where('division_id', '>', 0))
            ->with(['member:clan_id,name,rank,division_id', 'member.division:id,name,slug'])
            ->orderByDesc('created_at')
            ->paginate(50);

        $userMember = auth()->user()?->member;
        $userHasAward = $userMember
            ? MemberAward::where('award_id', $award->id)
                ->where('member_id', $userMember->clan_id)
                ->where('approved', true)
                ->exists()
            : false;

        $stats = (object) [
            'total' => $recipients->total(),
            'firstAwarded' => MemberAward::where('award_id', $award->id)
                ->where('approved', true)
                ->orderBy('created_at')
                ->first()?->created_at,
            'lastAwarded' => MemberAward::where('award_id', $award->id)
                ->where('approved', true)
                ->orderByDesc('created_at')
                ->first()?->created_at,
            'rarity' => $award->getRarity(),
        ];

        return view('division.awards.show', compact('award', 'recipients', 'stats', 'userHasAward'));
    }

    public function storeRecommendation(Request $request, Award $award)
    {
        if (! $award->allow_request) {
            return redirect()->back()->withErrors(['award' => 'Award requests are not allowed for this award.']);
        }

        if ($award->division && ! $award->division->active) {
            return redirect()->back()->withErrors(['award' => 'This is a legacy award and cannot be requested.']);
        }

        $validatedData = $request->validate([
            'reason' => 'required|string|max:255',
            'member_id' => [
                'required',
                'numeric',
                'exists:members,clan_id',
                new UniqueAwardForMember($award->id),
            ],
        ]);

        MemberAward::create([
            'requester_id' => auth()->user()->member_id,
            'award_id' => $award->id,
            'member_id' => $validatedData['member_id'],
            'reason' => $validatedData['reason'],
        ]);

        $this->showSuccessToast('Your award request has been submitted successfully.');

        return redirect()->back();
    }
}
