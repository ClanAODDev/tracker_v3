<?php

namespace App\Data;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Support\Collection;

class InactiveMembersData
{
    private int $inactivityDays;

    private Collection $allInactiveMembers;

    private Collection $inactiveDiscordMembers;

    private Collection $flaggedMembers;

    public function __construct(private Division $division)
    {
        $this->inactivityDays = $division->settings()->inactivity_days;

        $this->allInactiveMembers     = $this->getInactiveMembers($division, $this->inactivityDays);
        $this->inactiveDiscordMembers = request()->platoon
            ? $this->allInactiveMembers->where('platoon_id', request()->platoon->id)->values()
            : $this->allInactiveMembers;

        $this->flaggedMembers = $division->members()
            ->whereFlaggedForInactivity(true)
            ->with(['squad', 'platoon', 'leave'])
            ->get();
    }

    public static function for(Division $division): self
    {
        return new self($division);
    }

    public function toArray(): array
    {
        $division       = $this->division;
        $inactivityDays = $this->inactivityDays;
        $user           = auth()->user();

        return [
            'division' => [
                'name'           => $division->name,
                'slug'           => $division->slug,
                'platoonLabel'   => $division->locality('Platoon'),
                'inactivityDays' => $inactivityDays,
            ],
            'stats'         => $this->buildStats($this->allInactiveMembers, $this->flaggedMembers, $inactivityDays),
            'activePlatoon' => request()->platoon?->id,
            'platoons'      => $division->platoons->map(fn ($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'count' => $this->allInactiveMembers->where('platoon_id', $p->id)->count(),
            ])->values(),
            'inactive' => $this->inactiveDiscordMembers
                ->map(fn ($m) => $this->row($m, $division, $inactivityDays))->values(),
            'flagged' => $this->flaggedMembers
                ->map(fn ($m) => $this->row($m, $division, $inactivityDays, flagged: true))->values(),
            'activityLog' => $this->getRecentFlagActivity($division)
                ->filter(fn ($a) => isset($a->subject->name))
                ->map(fn ($a) => [
                    'icon' => $a->name->feedIcon(),
                    'user' => $a->user?->name ?? 'Unknown',
                    'verb' => match ($a->name) {
                        ActivityType::FLAGGED   => 'flagged',
                        ActivityType::UNFLAGGED => 'unflagged',
                        ActivityType::REMOVED   => 'removed',
                        default                 => 'updated',
                    },
                    'subject' => $a->subject->name,
                    'when'    => $a->created_at->diffForHumans(),
                ])->values(),
            'can' => [
                'remind' => $user->can('remindActivity', Member::class),
                'flag'   => $user->can('flag-inactive', Member::class),
            ],
            'bulk' => [
                'pm'       => route('private-message.create', ['division' => $division]),
                'reminder' => route('bulk-reminder.store', $division),
                'flag'     => route('inactive.bulk-flag', $division),
                'unflag'   => route('inactive.bulk-unflag', $division),
            ],
        ];
    }

    private function row(Member $member, Division $division, int $inactivityDays, bool $flagged = false): array
    {
        $days     = $member->last_voice_activity?->diffInDays(now());
        $severity = $days === null || $days >= $inactivityDays * 2
            ? 'severe'
            : ($days >= $inactivityDays * 1.5 ? 'warning' : 'normal');
        $reminder = $member->last_activity_reminder_at;
        $user     = auth()->user();

        return [
            'id'         => $member->clan_id,
            'name'       => $member->name,
            'rankAbbr'   => $member->rank->getAbbreviation(),
            'profileUrl' => route('member', $member->getUrlParams()),
            'voice'      => [
                'label' => $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks', 'months']),
                'iso'   => $member->last_voice_activity?->toIso8601String(),
            ],
            'reminder' => [
                'date'          => $reminder?->format('n/j/y'),
                'remindedToday' => (bool) $reminder?->isToday(),
                'human'         => $reminder ? 'Reminded ' . $reminder->diffForHumans() : 'Not reminded',
            ],
            'status'     => $member->last_voice_status?->getLabel() ?? 'Unknown',
            'unit'       => trim(($member->platoon->name ?? 'Unassigned') . ($member->squad ? ' / ' . $member->squad->name : '')),
            'severity'   => $severity,
            'forumPmUrl' => doForumFunction([$member->clan_id], 'pm'),
            'flagUrl'    => route('member.flag-inactive', $member->clan_id),
            'unflagUrl'  => $flagged ? route('member.unflag-inactive', $member->clan_id) : null,
            'removeUrl'  => $flagged && $user->can('separate', $member) ? route('member.drop-for-inactivity', $member->clan_id) : null,
            'canRemind'  => $user->can('remindActivity', $member),
            'canFlag'    => $user->can('flag-inactive', $member),
        ];
    }

    private function getInactiveMembers(Division $division, int $inactivityDays): Collection
    {
        $threshold = now()->subDays($inactivityDays);

        return $division->members()
            ->where(function ($query) use ($threshold) {
                $query->where('last_voice_activity', '<', $threshold)
                    ->orWhereNull('last_voice_activity');
            })
            ->where('flagged_for_inactivity', false)
            ->whereDoesntHave('leave', fn ($q) => $q->whereDate('end_date', '>', today()))
            ->with(['squad', 'platoon'])
            ->orderBy('last_voice_activity')
            ->get();
    }

    private function getRecentFlagActivity(Division $division): Collection
    {
        return Activity::where('division_id', $division->id)
            ->whereIn('name', [ActivityType::FLAGGED, ActivityType::UNFLAGGED, ActivityType::REMOVED])
            ->orderByDesc('created_at')
            ->with(['subject', 'user'])
            ->take(20)
            ->get();
    }

    private function buildStats(Collection $inactive, Collection $flagged, int $inactivityDays): array
    {
        $severeThreshold = now()->subDays($inactivityDays * 2);

        return [
            'total'     => $inactive->count(),
            'flagged'   => $flagged->count(),
            'byPlatoon' => $inactive->groupBy('platoon_id')->map->count(),
            'severe'    => $inactive->filter(
                fn ($m) => $m->last_voice_activity === null || $m->last_voice_activity < $severeThreshold
            )->count(),
        ];
    }
}
