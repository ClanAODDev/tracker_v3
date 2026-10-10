<?php

namespace App\Support;

use App\Models\Division;
use App\Models\Handle;
use App\Models\Leave;
use App\Models\Member;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
        (new EloquentCollection($members->all()))->load('ledUnit');

        return $members->map(fn (Member $member) => $this->row($member))->values()->all();
    }

    public function row(Member $member): array
    {
        $isParttimer = $member->division_id !== $this->division->id;
        $reminder    = $member->last_activity_reminder_at;

        return [
            'id'             => $member->clan_id,
            'name'           => $member->name,
            'rankName'       => $member->present()->rankName(),
            'rankAbbr'       => $member->rank->getAbbreviation(),
            'rankValue'      => $member->rank->value,
            'rankColor'      => $member->rank->getColorHex(),
            'position'       => $member->positionLabel(),
            'positionAbbr'   => $member->positionAbbreviation(),
            'positionClass'  => $member->position?->getClass(),
            'profileUrl'     => route('member', $member->getUrlParams()),
            'assignment'     => $this->assignment($member),
            'joinDate'       => $member->join_date?->format('Y-m-d'),
            'voice'          => $this->voice($member),
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
            'tagIds'  => $member->tags->pluck('id')->all(),
            'handles' => $this->division->handlesOf($member)
                ->mapWithKeys(fn (Handle $handle) => [$handle->id => [
                    'value' => $handle->pivot->value,
                    'url'   => $handle->full_url,
                    'error' => $handle->matches($handle->pivot->value)
                        ? null
                        : ($handle->regex_hint ?: "Does not match the {$handle->label} format"),
                ]])
                ->all(),
            'posts' => $member->posts,
            'leave' => $member->leave ? [
                'until'   => $member->leave->end_date?->format('M j'),
                'reason'  => Leave::$reasons[$member->leave->reason] ?? null,
                'pending' => $member->leave->approver_id === null,
            ] : null,
            'customFields' => $member->fieldValues
                ->mapWithKeys(fn ($value) => [$value->field->key => $value->value])
                ->all(),
            'isParttimer'     => $isParttimer,
            'primaryDivision' => $isParttimer ? ($member->division?->name ?? 'None') : null,
            'directRecruit'   => $this->directRecruitOfClanId !== null
                && $member->recruiter_id === $this->directRecruitOfClanId,
        ];
    }

    private function voice(Member $member): array
    {
        $bucket = $member->present()->activityBucket($this->division);

        return [
            'label'  => $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks', 'months']),
            'tone'   => ['text-success', 'text-warning', 'text-destructive', 'text-muted-foreground'][$bucket],
            'bucket' => $bucket,
            'iso'    => $member->last_voice_activity?->toIso8601String(),
        ];
    }

    private function assignment(Member $member): ?array
    {
        $unit = $this->assignmentKind === 'squad' ? $member->squadUnit() : $member->platoonUnit();

        return $unit
            ? [
                'label' => $unit->name ?: 'Untitled',
                'url'   => $unit->url($this->division),
            ]
            : null;
    }
}
