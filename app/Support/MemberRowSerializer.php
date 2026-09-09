<?php

namespace App\Support;

use App\Models\Division;
use App\Models\Member;
use Illuminate\Support\Collection;

class MemberRowSerializer
{
    public function __construct(
        private Division $division,
        private string $assignmentKind = 'platoon',
        private ?int $directRecruitOfClanId = null,
    ) {}

    public function collection(Collection $members): array
    {
        return $members->map(fn (Member $member) => $this->row($member))->values()->all();
    }

    public function row(Member $member): array
    {
        $isParttimer = $member->division_id !== $this->division->id;
        $reminder    = $member->last_activity_reminder_at;

        return [
            'id'            => $member->clan_id,
            'name'          => $member->name,
            'rankName'      => $member->present()->rankName(),
            'rankAbbr'      => $member->rank->getAbbreviation(),
            'rankValue'     => $member->rank->value,
            'rankColor'     => $member->rank->getColorHex(),
            'position'      => $member->position?->getLabel(),
            'positionAbbr'  => $member->position?->getAbbreviation() ?: null,
            'positionClass' => $member->position?->getClass(),
            'profileUrl'    => route('member', $member->getUrlParams()),
            'assignment'    => $this->assignment($member),
            'joinDate'      => $member->join_date?->format('Y-m-d'),
            'voice'         => [
                'label' => $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks', 'months']),
                'tone'  => $member->present()->activityClass($this->division),
                'iso'   => $member->last_voice_activity?->toIso8601String(),
            ],
            'lastPromotedAt' => $member->last_promoted_at?->format('Y-m-d'),
            'reminder'       => [
                'date'          => $reminder?->format('n/j/y'),
                'remindedToday' => (bool) $reminder?->isToday(),
                'human'         => $reminder ? 'Reminded ' . $reminder->diffForHumans() : 'Not reminded',
                'sortKey'       => $reminder?->format('Y-m-d') ?? '',
            ],
            'canRemind' => auth()->user()->can('remindActivity', $member),
            'tags'      => $member->tags
                ->filter(fn ($tag) => $tag->isVisibleTo())
                ->map(fn ($tag) => [
                    'id'         => $tag->id,
                    'name'       => $tag->name,
                    'visibility' => $tag->visibility->value,
                ])->values()->all(),
            'tagIds' => $member->tags->pluck('id')->all(),
            'handle' => $member->handle
                ? ['value' => $member->handle->pivot->value, 'url' => $member->handle->url ? $member->handle->full_url : null]
                : null,
            'posts'           => $member->posts,
            'onLeave'         => (bool) $member->leave,
            'isParttimer'     => $isParttimer,
            'primaryDivision' => $isParttimer ? ($member->division?->name ?? 'None') : null,
            'directRecruit'   => $this->directRecruitOfClanId !== null
                && $member->recruiter_id === $this->directRecruitOfClanId,
        ];
    }

    private function assignment(Member $member): ?array
    {
        if ($this->assignmentKind === 'squad') {
            return $member->squad && $member->platoon
                ? [
                    'label' => $member->squad->name ?: 'Untitled',
                    'url'   => route('squad.show', [$this->division->slug, $member->platoon, $member->squad]),
                ]
                : null;
        }

        return $member->platoon
            ? [
                'label' => $member->platoon->name ?: 'Untitled',
                'url'   => route('platoon', [$this->division->slug, $member->platoon]),
            ]
            : null;
    }
}
