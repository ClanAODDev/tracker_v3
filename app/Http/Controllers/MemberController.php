<?php

namespace App\Http\Controllers;

use App\Data\MemberStatsData;
use App\Data\NoteStatsData;
use App\Enums\ActivityType;
use App\Enums\Rank;
use App\Enums\TagVisibility;
use App\Models\Division;
use App\Models\DivisionTag;
use App\Models\Member;
use App\Models\Note;
use App\Models\Platoon;
use App\Models\User;
use App\Repositories\MemberRepository;
use App\Services\RankTimelineService;
use App\Support\MemberCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class MemberController extends Controller
{
    public function __construct(
        private MemberRepository $memberRepository,
        private RankTimelineService $rankTimelineService,
    ) {}

    public function search(Request $request): Response|JsonResponse
    {
        $query   = $request->query('q');
        $members = $query ? $this->memberRepository->search($query) : collect();

        $results = $members->values()->map(fn (Member $member) => [
            'rankName'   => $member->present()->rankName(),
            'clanId'     => $member->clan_id,
            'division'   => $member->division->name ?? 'Ex-AOD',
            'profileUrl' => route('member', $member->getUrlParams()),
            'discord'    => $member->discord,
            'handle'     => $member->handles->first()
                ? $member->handles->first()->pivot->value . ' [' . $member->handles->first()->label . ']'
                : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['results' => $results]);
        }

        return Inertia::render('member/search', [
            'query'   => $query,
            'results' => $results,
        ]);
    }

    public function searchAutoComplete(Request $request)
    {
        return $this->memberRepository->searchAutocomplete($request->input('query'));
    }

    public function show(Member $member): Response
    {
        $user           = auth()->user();
        $canViewSrLdr   = $user->isRole(['sr_ldr', 'admin']);
        $canViewNotes   = $user->can('create', Note::class);
        $canViewTrashed = $user->can('viewTrashed', Note::class);

        $this->memberRepository->loadProfileRelations($member);
        $division = $member->division;

        $notes             = $canViewNotes ? $this->memberRepository->getNotesForMember($member, $canViewSrLdr) : collect();
        $trashedNotes      = $canViewTrashed ? $this->memberRepository->getTrashedNotesForMember($member) : collect();
        $rankHistory       = $this->memberRepository->getRankHistory($member);
        $transfers         = $this->memberRepository->getTransfers($member);
        $partTimeDivisions = $member->partTimeDivisions()->whereActive(true)->get();

        $memberStats  = MemberStatsData::fromMember($member, $division, $this->memberRepository);
        $noteStats    = NoteStatsData::fromNotes($notes);
        $rankTimeline = $this->rankTimelineService->buildTimeline($member, $rankHistory);

        $isOwnProfile   = $user->member?->id === $member->id;
        $canFullHistory = ! $user->isRole('member') || $isOwnProfile;
        $notePayload    = fn (Note $note, bool $trashed = false) => [
            'id'             => $note->id,
            'type'           => $note->type,
            'body'           => $note->body,
            'authorName'     => $note->author?->name,
            'authorUrl'      => $note->author?->member ? route('member', $note->author->member->getUrlParams()) : null,
            'authorAvatar'   => $note->author?->member?->getDiscordAvatarUrl(),
            'createdAt'      => $note->created_at->diffForHumans(),
            'createdAtFull'  => $note->created_at->format('M j, Y g:i A'),
            'forumThreadUrl' => $note->forum_thread_id ? doForumFunction([$note->forum_thread_id], 'showThread') : null,
            'canDelete'      => ! $trashed && $user->can('delete', $note),
            'deleteUrl'      => route('deleteNote', [$member->clan_id, $note->id]),
            'deletedAt'      => $trashed ? $note->deleted_at->diffForHumans() : null,
            'restoreUrl'     => $trashed ? route('restoreNote', [$member->clan_id, $note->id]) : null,
            'forceDeleteUrl' => $trashed ? route('forceDeleteNote', [$member->clan_id, $note->id]) : null,
        ];

        return Inertia::render('member/show', [
            'member' => [
                ...MemberCard::from($member),
                'status'          => $member->isPending ? 'pending' : ($member->division_id === 0 ? 'ex-aod' : 'active'),
                'forumPmUrl'      => doForumFunction([$member->clan_id], 'pm'),
                'forumProfileUrl' => doForumFunction([$member->clan_id], 'forumProfile'),
                'hasAccount'      => (bool) $member->user,
            ],
            'actions' => array_values(array_filter([
                $user->can('update', $member)
                    ? ['label' => 'Edit member', 'url' => route('filament.mod.resources.members.edit', $member), 'external' => true]
                    : null,
                $user->can('update', $member) && $user->can('train', $user) && $member->rank->isAtLeast(Rank::SERGEANT)
                    ? ['label' => 'SGT training', 'url' => route('training.sgt', ['clan_id' => $member->clan_id])]
                    : null,
                $member->user && $user->can('impersonate', $member->user)
                    ? ['label' => 'Impersonate user', 'url' => route('impersonate', $member->user)]
                    : null,
                $user->can('flagInactive', Member::class)
                    ? ['label' => 'Flag for inactivity', 'url' => route('member.flag-inactive', $member->clan_id)]
                    : null,
            ])),
            'tags' => $member->tags
                ->filter(fn ($tag) => $tag->isVisibleTo())
                ->map(fn ($tag) => [
                    'id'         => $tag->id,
                    'name'       => $tag->name,
                    'visibility' => $tag->visibility->value,
                    'division'   => $tag->division?->name,
                ])->values(),
            'tagManagement'  => $this->buildTagManagement($member, $user),
            'breadcrumbs'    => $this->buildBreadcrumbs($member, $division),
            'notices'        => $this->buildNotices($member, $division, $user),
            'canCreateNote'  => $canViewNotes,
            'canViewTrashed' => $canViewTrashed,
            'noteTypes'      => Note::allNoteTypes(),
            'stats'          => [
                'tenure' => [
                    'years'       => $memberStats->tenure->years,
                    'months'      => $memberStats->tenure->months,
                    'joinDate'    => $memberStats->tenure->joinDate?->format('M j, Y'),
                    'recruitedBy' => $member->recruiter_id !== 0 ? MemberCard::ref($member->recruiter) : null,
                    'trainedOn'   => $member->last_trained_at?->format('M j, Y'),
                    'trainedBy'   => MemberCard::ref($member->trainer),
                ],
                'activity' => [
                    'daysSinceVoice' => $memberStats->activity->daysSinceVoice,
                    'health'         => $memberStats->activity->health,
                    'healthPct'      => $memberStats->activity->healthPct,
                    'divisionMax'    => $memberStats->activity->divisionMax,
                    'reminders'      => ! $user->isRole('member')
                        ? $member->activityReminders->map(fn ($r) => [
                            'date' => $r->created_at->format('M j, Y'),
                            'by'   => $r->remindedBy?->name ?? 'Unknown',
                        ])->values()
                        : [],
                    'canClearReminders' => $user->isRole(['sr_ldr', 'admin']) && $user->member?->clan_id !== $member->clan_id,
                    'clearRemindersUrl' => route('member.clear-activity-reminders', $member->clan_id),
                ],
                'recruiting' => [
                    'total'         => $memberStats->recruiting->total,
                    'active'        => $memberStats->recruiting->active,
                    'retentionRate' => $memberStats->recruiting->retentionRate,
                    'recruits'      => $member->recruits->map(fn ($r) => [
                        ...MemberCard::ref($r),
                        'joinDate' => $r->join_date?->format('M j, Y'),
                        'division' => $r->division->name ?? 'Ex-AOD',
                        'active'   => (bool) $r->division,
                    ])->values(),
                ],
                'notes' => [
                    'total'      => $noteStats->total,
                    'positive'   => $noteStats->positive,
                    'negative'   => $noteStats->negative,
                    'misc'       => $noteStats->misc,
                    'srLdr'      => $noteStats->sr_ldr,
                    'latestType' => $noteStats->latestType,
                ],
            ],
            'awards' => [
                'total'    => $memberStats->awards->total,
                'byRarity' => $memberStats->awards->byRarity,
                'list'     => $this->buildAwards($member),
            ],
            'rankTimeline'       => $rankTimeline,
            'canFullHistory'     => $canFullHistory,
            'divisionComparison' => $memberStats->divisionComparison ? [
                'divisionName'       => $division->name,
                'tenurePercentile'   => $memberStats->divisionComparison->tenurePercentile,
                'tenureBetter'       => $memberStats->divisionComparison->tenureBetter,
                'avgTenureYears'     => $memberStats->divisionComparison->avgTenureYears,
                'activityPercentile' => $memberStats->divisionComparison->activityPercentile,
                'activityBetter'     => $memberStats->divisionComparison->activityBetter,
                'avgVoiceDays'       => $memberStats->divisionComparison->avgVoiceDays,
                'hasActivity'        => $memberStats->activity->daysSinceVoice !== null,
            ] : null,
            'handles' => [
                'discord'    => $member->discord,
                'discordUrl' => $member->getDiscordUrl(),
                'groups'     => $member->handles->groupBy('label')->map(function ($handles, $label) {
                    $primary = $handles->first();

                    return [
                        'label'  => $label,
                        'value'  => $primary->pivot->value,
                        'url'    => $primary->url ? $primary->full_url : null,
                        'extras' => $handles->slice(1)->map(fn ($h) => [
                            'value' => $h->pivot->value,
                            'url'   => $h->url ? $h->full_url : null,
                        ])->values(),
                    ];
                })->values(),
            ],
            'divisions' => [
                'current' => $division && $member->division_id !== 0 && ! $member->isPending
                    ? [
                        'name'  => $division->name,
                        'slug'  => $division->slug,
                        'logo'  => $division->getLogoPath(),
                        'since' => $transfers->sortByDesc('created_at')->first()?->created_at?->format('M Y'),
                    ]
                    : null,
                'partTime' => $partTimeDivisions->map(fn ($d) => [
                    'name'  => $d->name,
                    'slug'  => $d->slug,
                    'logo'  => $d->getLogoPath(),
                    'since' => $d->pivot->created_at?->format('M Y'),
                ])->values(),
                'past' => $this->buildPastDivisions($transfers, $division, $member->isPending || $member->division_id === 0),
            ],
            'notes'        => $notes->map(fn (Note $n) => $notePayload($n))->values(),
            'trashedNotes' => $trashedNotes->map(fn (Note $n) => $notePayload($n, true))->values(),
        ]);
    }

    private function buildNotices(Member $member, ?Division $division, $user): array
    {
        $notices = [];

        if ($member->memberRequest && $member->memberRequest->approved_at === null) {
            $notices[] = [
                'type'    => 'warning',
                'message' => "This member's status request is currently pending. Some details, like Discord VoIP activity, may not be available until the request is approved.",
            ];
        }

        if ($user->can('update', $member) && $division?->handle && ! $member->handles->contains($division->handle)) {
            $notices[] = [
                'type'     => 'warning',
                'message'  => "The {$division->name} division requires a {$division->handle->label} handle, but {$member->name} does not have one.",
                'ctaLabel' => 'Add handle',
                'ctaUrl'   => route('filament.mod.resources.members.edit', $member) . '#ingame-handles',
            ];
        }

        if ($member->flagged_for_inactivity) {
            $notices[] = [
                'type'     => 'warning',
                'message'  => 'Member is flagged for removal due to inactivity.',
                'ctaLabel' => 'Remove flag',
                'ctaUrl'   => route('member.unflag-inactive', $member->clan_id) . '#flagged',
            ];
        }

        if ($member->leave && $user->can('update', $member->leave)) {
            $notices[] = [
                'type'    => 'warning',
                'message' => $member->leave->approver
                    ? "Member has a leave of absence for {$member->leave->reason} until {$member->leave->end_date->format('Y-m-d')}."
                    : 'Member has a leave of absence request that has not yet been approved.',
                'ctaLabel' => 'View details',
                'ctaUrl'   => route('filament.mod.resources.leaves.edit', $member->leave->id),
            ];
        }

        return $notices;
    }

    private function buildBreadcrumbs(Member $member, ?Division $division): array
    {
        $crumbs = [];

        if ($division) {
            $crumbs[] = ['label' => $division->name, 'href' => route('division', $division->slug)];

            if ($member->platoon_id !== 0 && $member->platoon) {
                $crumbs[] = ['label' => $member->platoon->name, 'href' => route('platoon', [$division->slug, $member->platoon->id])];
            }

            if ($member->squad_id !== 0 && $member->squad) {
                $crumbs[] = [
                    'label' => $member->squad->name ?: 'Untitled',
                    'href'  => route('squad.show', [$division->slug, $member->platoon->id, $member->squad]),
                ];
            }
        }

        $crumbs[] = ['label' => 'View profile'];

        return $crumbs;
    }

    private function buildTagManagement(Member $member, User $user): ?array
    {
        if (! $user->can('assign', [DivisionTag::class, $member])) {
            return null;
        }

        $division = $member->division ?? $user->member?->division;

        if (! $division) {
            return null;
        }

        $canCreate = $user->can('create', DivisionTag::class);

        return [
            'getUrl'            => route('member-tags.get', [$division, $member->clan_id]),
            'addUrl'            => route('member-tags.add', [$division, $member->clan_id]),
            'removeUrl'         => route('member-tags.remove', [$division, $member->clan_id]),
            'createUrl'         => route('member-tags.create', [$division, $member->clan_id]),
            'canCreate'         => $canCreate,
            'visibilityOptions' => collect(TagVisibility::cases())
                ->filter(fn (TagVisibility $v) => $v !== TagVisibility::SENIOR_LEADERS || $user->isRole(['sr_ldr', 'admin']))
                ->map(fn (TagVisibility $v) => ['value' => $v->value, 'label' => $v->label()])
                ->values()
                ->all(),
        ];
    }

    private function buildAwards(Member $member): Collection
    {
        $memberAwardIds = $member->awards->pluck('award_id')->unique();

        $grouped = $member->awards->groupBy('award_id')->map(fn ($records) => [
            'award'  => $records->first()->award,
            'count'  => $records->count(),
            'latest' => $records->sortByDesc('created_at')->first(),
        ]);

        $skipIds      = collect();
        $tieredGroups = [];

        foreach ($grouped as $awardId => $group) {
            $award = $group['award'];
            $chain = $award->getPrerequisiteChain();

            if (count($chain) === 0) {
                continue;
            }

            $earnedInChain = collect([$award])->merge($chain)
                ->filter(fn ($a) => $memberAwardIds->contains($a->id))
                ->sortByDesc(fn ($a) => count($a->getPrerequisiteChain()));

            if ($earnedInChain->first()?->id === $award->id) {
                $tieredGroups[$awardId] = $earnedInChain->values();
                $skipIds                = $skipIds->merge($earnedInChain->skip(1)->pluck('id'));
            }
        }

        return $grouped
            ->reject(fn ($group, $id) => $skipIds->contains($id))
            ->sortBy('award.display_order')
            ->map(function ($group, $awardId) {
                $award  = $group['award'];
                $rarity = $award->getRarity();
                $tiers  = $tieredGroups[$awardId] ?? collect();
                $image  = fn ($a) => $a->image && Storage::disk('public')->exists($a->image) ? $a->getImagePath() : null;

                return [
                    'id'       => $award->id,
                    'name'     => $award->name,
                    'rarity'   => $rarity,
                    'image'    => $image($award),
                    'count'    => $group['count'],
                    'earnedOn' => $group['latest']->created_at->format('M j, Y'),
                    'reason'   => $group['latest']->reason ?? $award->description,
                    'url'      => $tiers->count() > 1
                        ? route('awards.tiered', $award->getTieredGroupSlug())
                        : route('awards.show', $award),
                    'tiers' => $tiers->count() > 1
                        ? $tiers->map(fn ($t) => ['name' => $t->name, 'image' => $image($t)])->values()
                        : null,
                ];
            })
            ->values();
    }

    private function buildPastDivisions(Collection $transfers, ?Division $division, bool $divisionless): Collection
    {
        if ($transfers->count() < 2) {
            return collect();
        }

        $sorted = $transfers->sortBy('created_at')->values();
        $spans  = collect();

        for ($i = 0; $i < $sorted->count() - 1; $i++) {
            $spans->push([
                'division' => $sorted[$i]->division,
                'days'     => $sorted[$i]->created_at->diffInDays($sorted[$i + 1]->created_at),
            ]);
        }

        return $spans
            ->filter(fn ($span) => $span['division'] !== null)
            ->groupBy(fn ($span) => $span['division']->id)
            ->when(! $divisionless && $division, fn ($groups) => $groups->reject(fn ($items, $id) => (int) $id === $division->id))
            ->map(function ($items) {
                $totalDays = (int) $items->sum('days');
                $years     = intdiv($totalDays, 365);
                $months    = intdiv($totalDays % 365, 30);
                $duration  = trim(($years ? "{$years}y " : '') . ($months ? "{$months}m" : '')) ?: '<1m';

                return [
                    'name'      => $items->first()['division']->name,
                    'slug'      => $items->first()['division']->slug,
                    'logo'      => $items->first()['division']->getLogoPath(),
                    'duration'  => $duration,
                    'visits'    => $items->count(),
                    'totalDays' => $totalDays,
                ];
            })
            ->sortByDesc('totalDays')
            ->values();
    }

    #[Authorize('recruit', Member::class)]
    public function assignPlatoon(Member $member): JsonResponse
    {
        $platoon = Platoon::where('id', request()->platoon_id)
            ->where('division_id', $member->division_id)
            ->firstOrFail();

        $member->platoon_id = $platoon->id;
        $member->save();
        $member->recordActivity(ActivityType::ASSIGNED_PLATOON, [
            'platoon' => $platoon->name,
        ]);

        return response()->json(['success' => true]);
    }

    public function confirmUnassign(Member $member): Response
    {
        $this->authorize('reset', $member);

        return Inertia::render('member/confirm-unassign', [
            'member'   => MemberCard::from($member),
            'resetUrl' => route('member.unassign', $member->clan_id),
        ]);
    }

    public function unassignMember(Member $member): RedirectResponse
    {
        $this->authorize('reset', $member);

        $member->squad_id   = 0;
        $member->platoon_id = 0;
        $member->save();
        $member->recordActivity(ActivityType::UNASSIGNED);

        $this->showSuccessToast('Member assignments reset successfully');

        return redirect()->route('member', $member->getUrlParams());
    }
}
