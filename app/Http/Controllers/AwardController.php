<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\Member;
use App\Models\MemberAward;
use App\Rules\UniqueAwardForMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class AwardController extends Controller
{
    public function index(): Response|RedirectResponse
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
            $award->rarity        = $award->getRarity();
        });

        $clanAwards = $awards->whereNull('division_id')->values();

        $activeAwards = $awards
            ->whereNotNull('division_id')
            ->filter(fn ($award) => $award->division?->active || $award->division?->slug === $divisionSlug)
            ->groupBy('division.name')
            ->sortKeys();

        $legacyAwards = $awards
            ->whereNotNull('division_id')
            ->filter(fn ($award) => ! $award->division?->active && $award->recipients_count > 0 && $award->division?->slug !== $divisionSlug)
            ->groupBy('division.name')
            ->sortKeys();

        $activeAndClanAwards = $awards->filter(fn ($a) => $a->division_id === null || $a->division?->active);
        $totals              = [
            'awards'      => $awards->count(),
            'recipients'  => $awards->sum('recipients_count'),
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
            ->values()
            ->map(fn ($division) => [
                'name'   => $division->name,
                'slug'   => $division->slug,
                'active' => (bool) $division->active,
            ]);

        $tieredGroups   = collect($this->buildTieredGroups());
        $tieredAwardIds = $tieredGroups->flatMap(fn ($group) => $group['tiers']->pluck('id'))->toArray();

        $clanAwards   = $clanAwards->reject(fn ($a) => in_array($a->id, $tieredAwardIds));
        $activeAwards = $activeAwards
            ->map(fn ($group) => $group->reject(fn ($a) => in_array($a->id, $tieredAwardIds)))
            ->filter(fn ($group) => $group->isNotEmpty());
        $legacyAwards = $legacyAwards
            ->map(fn ($group) => $group->reject(fn ($a) => in_array($a->id, $tieredAwardIds)))
            ->filter(fn ($group) => $group->isNotEmpty());

        return Inertia::render('awards/index', [
            'divisionSlug'    => $divisionSlug,
            'totals'          => $totals,
            'rarityBreakdown' => $awards->groupBy('rarity')->map(fn ($group) => $group->count()),
            'rarities'        => collect(config('aod.awards.rarity'))->map(fn ($r, $key) => [
                'key'   => $key,
                'label' => $r['label'],
                'min'   => $r['min'],
                'max'   => $r['max'],
            ])->values(),
            'divisions' => $divisionsWithAwards,
            'clan'      => [
                'awards' => $clanAwards->values()->map(fn ($a) => $this->serializeAward($a)),
                'tiered' => $tieredGroups->whereNull('division_id')->values()->map(fn ($g) => $this->serializeTieredGroup($g)),
            ],
            'divisionSections' => $activeAwards->map(function ($group, $name) use ($tieredGroups) {
                $division = $group->first()->division;

                return [
                    'name'   => $name,
                    'logo'   => $division->getLogoPath(),
                    'awards' => $group->values()->map(fn ($a) => $this->serializeAward($a)),
                    'tiered' => $tieredGroups->where('division_id', $division->id)->values()
                        ->map(fn ($g) => $this->serializeTieredGroup($g)),
                ];
            })->values(),
            'legacySections' => $legacyAwards->map(function ($group, $name) {
                $division = $group->first()->division;

                return [
                    'name'   => $name,
                    'logo'   => $division->getLogoPath(),
                    'awards' => $group->values()->map(fn ($a) => $this->serializeAward($a, legacy: true)),
                ];
            })->values(),
        ]);
    }

    private function serializeAward(Award $award, bool $legacy = false): array
    {
        return [
            'id'   => $award->id,
            'name' => $award->division
                ? Str::replace($award->division->name . ' - ', '', $award->name)
                : $award->name,
            'rarity'          => $award->rarity,
            'recipientsCount' => (int) $award->recipients_count,
            'image'           => $award->image ? $award->getImagePath() : null,
            'allowRequest'    => ! $legacy && (bool) $award->allow_request,
        ];
    }

    private function serializeTieredGroup(array $group): array
    {
        return [
            'name'           => $group['name'],
            'slug'           => $group['slug'],
            'tierCount'      => $group['tiers']->count(),
            'recipientCount' => $group['recipientCount'],
            'image'          => $group['topTier']->image ? $group['topTier']->getImagePath() : null,
        ];
    }

    private function buildTieredGroups(): array
    {
        $awardsWithChains = Award::active()
            ->where(function ($q) {
                $q->whereNotNull('prerequisite_award_id')
                    ->orWhereHas('dependents');
            })
            ->withCount('recipients')
            ->with('division')
            ->orderBy('display_order')
            ->get();

        $awardsById   = $awardsWithChains->keyBy('id');
        $dependentMap = $awardsWithChains
            ->filter(fn ($a) => $a->prerequisite_award_id !== null)
            ->keyBy('prerequisite_award_id');

        // One query for every tiered award's recipients instead of one `count()`
        // query per chain below — grouped by award so each chain's distinct
        // member count can still be computed in memory.
        $recipientsByAward = MemberAward::whereIn('award_id', $awardsWithChains->pluck('id'))
            ->where('approved', true)
            ->whereHas('member', fn ($q) => $q->where('division_id', '>', 0))
            ->get(['award_id', 'member_id'])
            ->groupBy('award_id');

        $processed = [];
        $groups    = [];

        foreach ($awardsWithChains as $award) {
            if (in_array($award->id, $processed)) {
                continue;
            }

            $chain   = collect([$award]);
            $current = $awardsById->get($award->prerequisite_award_id);
            while ($current) {
                $chain->push($current);
                $current = $awardsById->get($current->prerequisite_award_id);
            }

            $current = $award;
            while ($next = $dependentMap->get($current->id)) {
                $chain->push($next);
                $current = $next;
            }

            $chain = $chain->unique('id')->sortBy('display_order')->values();

            $chainIds  = $chain->pluck('id')->toArray();
            $processed = array_merge($processed, $chainIds);

            $topTier        = $chain->last();
            $recipientCount = collect($chainIds)
                ->flatMap(fn ($id) => $recipientsByAward->get($id, collect())->pluck('member_id'))
                ->unique()
                ->count();

            $baseTier  = $chain->first();
            $groupName = $baseTier->tiered_group_name ?? $this->getTieredGroupName($chain);
            $division  = $baseTier->division;
            $groups[]  = [
                'name'           => $groupName,
                'slug'           => Str::slug($groupName),
                'description'    => $baseTier->tiered_group_description ?? $this->getTieredGroupDescription($chain, $groupName),
                'tiers'          => $chain,
                'topTier'        => $topTier,
                'recipientCount' => $recipientCount,
                'division_id'    => $division?->id,
                'division'       => $division,
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
        $firstWords  = explode(' ', $names[0]);
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
        $topTier   = $chain->last();

        if (str_contains($groupName, 'Tenure')) {
            return 'Recognition for years of dedicated service to the Angels of Death clan. Each tier represents a milestone in your AOD journey.';
        }

        return "A {$tierCount}-tier progression culminating in {$topTier->name}. Earn each tier in sequence to complete the set.";
    }

    public function tiered(string $slug): Response
    {
        $tieredGroups = $this->buildTieredGroups();
        $group        = collect($tieredGroups)->firstWhere('slug', $slug);

        abort_unless($group, 404);

        $tierIds = $group['tiers']->pluck('id')->toArray();
        $tiers   = Award::whereIn('id', $tierIds)
            ->withCount('recipients')
            ->orderBy('display_order')
            ->get();

        $userMember = auth()->user()?->member;

        $userAwards = $userMember
            ? MemberAward::where('member_id', $userMember->id)
                ->whereIn('award_id', $tierIds)
                ->where('approved', true)
                ->get()
                ->keyBy('award_id')
            : collect();

        $userAwardIds = $userAwards->pluck('award_id')->toArray();
        $earnedCount  = count($userAwardIds);
        $totalTiers   = $tiers->count();

        $nextTierId = $tiers->firstWhere(fn ($tier) => ! in_array($tier->id, $userAwardIds))?->id;

        $firstAwarded = MemberAward::whereIn('award_id', $tierIds)
            ->where('approved', true)
            ->orderBy('created_at')
            ->first()?->created_at;

        return Inertia::render('awards/tiered', [
            'group' => [
                'name'        => $group['name'],
                'slug'        => $group['slug'],
                'description' => $group['description'],
            ],
            'stats' => [
                'totalRecipients' => $group['recipientCount'],
                'firstAwarded'    => $firstAwarded?->format('M Y'),
                'earnedCount'     => $earnedCount,
                'totalTiers'      => $totalTiers,
                'progressPct'     => $totalTiers > 0 ? round(($earnedCount / $totalTiers) * 100) : 0,
            ],
            'tiers' => $tiers->map(function ($tier) use ($userAwards, $userAwardIds, $nextTierId, $group) {
                $earned = in_array($tier->id, $userAwardIds);

                return [
                    'id'              => $tier->id,
                    'name'            => $tier->name,
                    'description'     => $tier->description,
                    'rarity'          => $tier->getRarity(),
                    'recipientsCount' => (int) $tier->recipients_count,
                    'image'           => $tier->image ? $tier->getImagePath() : null,
                    'earned'          => $earned,
                    'isNext'          => $tier->id === $nextTierId,
                    'earnedDate'      => $earned ? $userAwards->get($tier->id)?->created_at?->format('M j, Y') : null,
                    'pct'             => $group['recipientCount'] > 0
                        ? round(($tier->recipients_count / $group['recipientCount']) * 100)
                        : 0,
                ];
            }),
        ]);
    }

    public function show(Award $award): Response
    {
        $award->load(['division']);
        $award->loadCount('recipients');

        $recipientsQuery = MemberAward::where('award_id', $award->id)
            ->where('approved', true)
            ->whereHas('member', fn ($q) => $q->where('division_id', '>', 0))
            ->with(['member:id,clan_id,name,rank,division_id,discord_id,discord_avatar', 'member.division:id,name,slug']);

        $recipients = $award->repeatable
            ? $recipientsQuery
                ->selectRaw('member_id, COUNT(*) as times_received, MAX(created_at) as last_awarded_at')
                ->groupBy('member_id')
                ->orderByDesc('times_received')
                ->paginate(50)
            : $recipientsQuery->orderByDesc('created_at')->paginate(50);

        $userMember   = auth()->user()?->member;
        $userHasAward = $userMember
            ? MemberAward::where('award_id', $award->id)
                ->where('member_id', $userMember->id)
                ->where('approved', true)
                ->exists()
            : false;

        return Inertia::render('awards/show', [
            'award' => [
                'id'           => $award->id,
                'name'         => $award->name,
                'description'  => $award->description,
                'rarity'       => $award->getRarity(),
                'image'        => $award->image ? $award->getImagePath() : null,
                'repeatable'   => (bool) $award->repeatable,
                'allowRequest' => (bool) $award->allow_request,
                'canRequest'   => $award->canBeRequestedBy(),
                'division'     => $award->division
                    ? ['name' => $award->division->name, 'slug' => $award->division->slug, 'active' => (bool) $award->division->active]
                    : null,
            ],
            'stats' => [
                'total'        => $recipients->total(),
                'firstAwarded' => MemberAward::where('award_id', $award->id)->where('approved', true)
                    ->orderBy('created_at')->first()?->created_at?->format('M Y'),
                'lastAwarded' => MemberAward::where('award_id', $award->id)->where('approved', true)
                    ->orderByDesc('created_at')->first()?->created_at?->format('M Y'),
                'rarity' => $award->getRarity(),
            ],
            'userHasAward'  => $userHasAward,
            'currentMember' => $userMember
                ? ['name' => $userMember->name, 'clanId' => $userMember->clan_id]
                : null,
            'recipients' => [
                'data' => collect($recipients->items())->map(fn ($record) => [
                    'name'        => $record->member?->name,
                    'url'         => $record->member ? route('member', $record->member->getUrlParams()) : null,
                    'avatarUrl'   => $record->member?->getDiscordAvatarUrl(),
                    'division'    => $record->member?->division?->name,
                    'divisionUrl' => $record->member?->division
                        ? route('division', $record->member->division->slug)
                        : null,
                    'rank'          => $record->member?->rank?->getAbbreviation(),
                    'timesReceived' => $award->repeatable ? (int) $record->times_received : null,
                    'awardedAt'     => $award->repeatable
                        ? Carbon::parse($record->last_awarded_at)->format('M j, Y')
                        : $record->created_at->format('M j, Y'),
                ]),
                'currentPage' => $recipients->currentPage(),
                'lastPage'    => $recipients->lastPage(),
            ],
        ]);
    }

    public function storeRecommendation(Request $request, Award $award): RedirectResponse
    {
        if (! $award->canBeRequestedBy()) {
            return redirect()->back()->withErrors(['award' => 'You cannot request this award.']);
        }

        $validatedData = $request->validate([
            'reason'    => 'required|string|max:255',
            'member_id' => [
                'required',
                'numeric',
                'exists:members,clan_id',
                new UniqueAwardForMember($award->id),
            ],
        ]);

        $member = Member::whereClanId($validatedData['member_id'])->firstOrFail();

        MemberAward::create([
            'requester_id' => auth()->user()->member_id,
            'award_id'     => $award->id,
            'member_id'    => $member->id,
            'reason'       => $validatedData['reason'],
        ]);

        $this->showSuccessToast('Your award request has been submitted successfully.');

        return redirect()->back();
    }
}
