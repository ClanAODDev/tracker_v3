<?php

namespace App\Data;

use App\Enums\Rank;
use App\Enums\TagVisibility;
use App\Models\Division;
use App\Models\DivisionTag;
use App\Models\Member;
use App\Models\Note;
use App\Models\User;
use App\Repositories\MemberRepository;
use App\Services\RankTimelineService;
use App\Support\MemberCard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MemberProfileData
{
    private User $user;

    private bool $canViewSrLdr;

    private bool $canViewNotes;

    private bool $canViewTrashed;

    private ?Division $division;

    private Collection $notes;

    private Collection $trashedNotes;

    private Collection $transfers;

    private Collection $partTimeDivisions;

    private MemberStatsData $stats;

    private NoteStatsData $noteStats;

    private object $rankTimeline;

    private bool $canFullHistory;

    public function __construct(
        private Member $member,
        MemberRepository $repository,
        RankTimelineService $rankTimelineService,
    ) {
        $this->user           = auth()->user();
        $this->canViewSrLdr   = $this->user->isRole(['sr_ldr', 'admin']);
        $this->canViewNotes   = $this->user->can('create', Note::class);
        $this->canViewTrashed = $this->user->can('viewTrashed', Note::class);

        $repository->loadProfileRelations($member);
        $this->division = $member->division;

        $this->notes             = $this->canViewNotes ? $repository->getNotesForMember($member, $this->canViewSrLdr) : collect();
        $this->trashedNotes      = $this->canViewTrashed ? $repository->getTrashedNotesForMember($member) : collect();
        $this->transfers         = $repository->getTransfers($member);
        $this->partTimeDivisions = $member->partTimeDivisions()->whereActive(true)->get();

        $this->stats        = MemberStatsData::fromMember($member, $this->division, $repository);
        $this->noteStats    = NoteStatsData::fromNotes($this->notes);
        $this->rankTimeline = $rankTimelineService->buildTimeline($member, $repository->getRankHistory($member));

        $isOwnProfile         = $this->user->member?->id === $member->id;
        $this->canFullHistory = ! $this->user->isRole('member') || $isOwnProfile;
    }

    public static function for(Member $member, MemberRepository $repository, RankTimelineService $rankTimelineService): self
    {
        return new self($member, $repository, $rankTimelineService);
    }

    public function toArray(): array
    {
        $member = $this->member;
        $user   = $this->user;

        return [
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
            'tagManagement'  => $this->tagManagement(),
            'breadcrumbs'    => $this->breadcrumbs(),
            'notices'        => $this->notices(),
            'canCreateNote'  => $this->canViewNotes,
            'canViewTrashed' => $this->canViewTrashed,
            'noteTypes'      => Note::allNoteTypes(),
            'stats'          => [
                'tenure' => [
                    'years'       => $this->stats->tenure->years,
                    'months'      => $this->stats->tenure->months,
                    'joinDate'    => $this->stats->tenure->joinDate?->format('M j, Y'),
                    'recruitedBy' => $member->recruiter_id !== 0 ? MemberCard::ref($member->recruiter) : null,
                    'trainedOn'   => $member->last_trained_at?->format('M j, Y'),
                    'trainedBy'   => MemberCard::ref($member->trainer),
                ],
                'activity' => [
                    'daysSinceVoice' => $this->stats->activity->daysSinceVoice,
                    'health'         => $this->stats->activity->health,
                    'healthPct'      => $this->stats->activity->healthPct,
                    'divisionMax'    => $this->stats->activity->divisionMax,
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
                    'total'         => $this->stats->recruiting->total,
                    'active'        => $this->stats->recruiting->active,
                    'retentionRate' => $this->stats->recruiting->retentionRate,
                    'recruits'      => $member->recruits->map(fn ($r) => [
                        ...MemberCard::ref($r),
                        'joinDate' => $r->join_date?->format('M j, Y'),
                        'division' => $r->division->name ?? 'Ex-AOD',
                        'active'   => (bool) $r->division,
                    ])->values(),
                ],
                'notes' => [
                    'total'      => $this->noteStats->total,
                    'positive'   => $this->noteStats->positive,
                    'negative'   => $this->noteStats->negative,
                    'misc'       => $this->noteStats->misc,
                    'srLdr'      => $this->noteStats->sr_ldr,
                    'latestType' => $this->noteStats->latestType,
                ],
            ],
            'awards' => [
                'total'    => $this->stats->awards->total,
                'byRarity' => $this->stats->awards->byRarity,
                'list'     => $this->awards(),
            ],
            'rankTimeline'       => $this->rankTimeline,
            'canFullHistory'     => $this->canFullHistory,
            'divisionComparison' => $this->stats->divisionComparison ? [
                'divisionName'       => $this->division->name,
                'tenurePercentile'   => $this->stats->divisionComparison->tenurePercentile,
                'tenureBetter'       => $this->stats->divisionComparison->tenureBetter,
                'avgTenureYears'     => $this->stats->divisionComparison->avgTenureYears,
                'activityPercentile' => $this->stats->divisionComparison->activityPercentile,
                'activityBetter'     => $this->stats->divisionComparison->activityBetter,
                'avgVoiceDays'       => $this->stats->divisionComparison->avgVoiceDays,
                'hasActivity'        => $this->stats->activity->daysSinceVoice !== null,
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
                'current' => $this->division && $member->division_id !== 0 && ! $member->isPending
                    ? [
                        'name'  => $this->division->name,
                        'slug'  => $this->division->slug,
                        'logo'  => $this->division->getLogoPath(),
                        'since' => $this->transfers->sortByDesc('created_at')->first()?->created_at?->format('M Y'),
                    ]
                    : null,
                'partTime' => $this->partTimeDivisions->map(fn ($d) => [
                    'name'  => $d->name,
                    'slug'  => $d->slug,
                    'logo'  => $d->getLogoPath(),
                    'since' => $d->pivot->created_at?->format('M Y'),
                ])->values(),
                'past' => $this->pastDivisions($member->isPending || $member->division_id === 0),
            ],
            'notes'        => $this->notes->map(fn (Note $n) => $this->notePayload($n))->values(),
            'trashedNotes' => $this->trashedNotes->map(fn (Note $n) => $this->notePayload($n, true))->values(),
        ];
    }

    private function notePayload(Note $note, bool $trashed = false): array
    {
        $member = $this->member;
        $user   = $this->user;

        return [
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
    }

    private function notices(): array
    {
        $member   = $this->member;
        $division = $this->division;
        $user     = $this->user;
        $notices  = [];

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

    private function breadcrumbs(): array
    {
        $member   = $this->member;
        $division = $this->division;
        $crumbs   = [];

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

    private function tagManagement(): ?array
    {
        $member = $this->member;
        $user   = $this->user;

        if (! $user->can('assign', [DivisionTag::class, $member])) {
            return null;
        }

        $division = $member->division ?? $user->member?->division;

        if (! $division) {
            return null;
        }

        return [
            'getUrl'            => route('member-tags.get', [$division, $member->clan_id]),
            'addUrl'            => route('member-tags.add', [$division, $member->clan_id]),
            'removeUrl'         => route('member-tags.remove', [$division, $member->clan_id]),
            'createUrl'         => route('member-tags.create', [$division, $member->clan_id]),
            'canCreate'         => $user->can('create', DivisionTag::class),
            'visibilityOptions' => collect(TagVisibility::cases())
                ->filter(fn (TagVisibility $v) => $v !== TagVisibility::SENIOR_LEADERS || $user->isRole(['sr_ldr', 'admin']))
                ->map(fn (TagVisibility $v) => ['value' => $v->value, 'label' => $v->label()])
                ->values()
                ->all(),
        ];
    }

    private function awards(): Collection
    {
        $member         = $this->member;
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
            ->map(function ($group, $awardId) use ($tieredGroups) {
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

    private function pastDivisions(bool $divisionless): Collection
    {
        $transfers = $this->transfers;
        $division  = $this->division;

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
}
