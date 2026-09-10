<?php

namespace App\Data;

use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\User;
use App\Support\DivisionToolbar;
use App\Support\MemberCard;
use Illuminate\Support\Collection;

readonly class DivisionShowData
{
    public function __construct(
        public Division $division,
        public DivisionStatsData $stats,
        public CensusChartData $chartData,
        public Collection $platoons,
        public Collection $divisionLeaders,
        public Collection $divisionAnniversaries,
        public ?object $previousCensus,
        public PendingActionsData $pendingActions,
        public Collection $recentActivity,
        public int $pendingApplicationCount,
    ) {}

    public function toArray(): array
    {
        $user     = auth()->user();
        $division = $this->division;

        return [
            'division' => [
                'name'                  => $division->name,
                'slug'                  => $division->slug,
                'abbr'                  => $division->abbreviation,
                'description'           => $division->description,
                'logo'                  => $division->getLogoPath(),
                'platoonLabel'          => $division->locality('platoon'),
                'isShutdown'            => $division->isShutdown(),
                'applicationRequired'   => (bool) $division->settings()->get('application_required', false),
                'applicationsUrl'       => url('/api/divisions/' . $division->slug . '/applications'),
                'canDeleteApplications' => $user->isRole(['sr_ldr', 'admin']),
                'canRecruit'            => $user->can('recruit', Member::class),
                'canCreatePlatoon'      => $user->can('create', [Platoon::class, $division]),
                'canManageUnassigned'   => $user->can('manageUnassigned', User::class),
                'editUrl'               => route('filament.mod.resources.divisions.edit', $division),
                'recruitUrl'            => route('recruiting.form', $division),
            ],
            'toolbar' => DivisionToolbar::for($division, $user),
            'stats'   => [
                'memberCount'           => $this->stats->memberCount,
                'voiceActiveCount'      => $this->stats->voiceActiveCount,
                'voiceRate'             => $this->stats->voiceRate,
                'recruitsThisMonth'     => $this->stats->recruitsThisMonth,
                'activityThresholdDays' => $this->stats->activityThresholdDays,
            ],
            'census' => [
                ...$this->chartData->toArray(),
                'previous' => $this->previousCensus
                    ? ['count' => $this->previousCensus->count, 'date' => $this->previousCensus->date]
                    : null,
            ],
            'leaders'  => $this->divisionLeaders->map(fn (Member $leader) => MemberCard::from($leader))->values(),
            'platoons' => $this->platoons->map(fn (Platoon $platoon) => [
                'id'          => $platoon->id,
                'name'        => $platoon->name,
                'description' => $platoon->description,
                'logo'        => $platoon->logo,
                'url'         => route('platoon', [$division->slug, $platoon->id]),
                'memberCount' => (int) $platoon->members_count,
                'voiceRate'   => $platoon->members_count > 0
                    ? (int) round(($platoon->voice_active_count / $platoon->members_count) * 100)
                    : 0,
                'leader' => MemberCard::from($platoon->leader),
                'squads' => $platoon->squads->map(fn ($squad) => [
                    'id'          => $squad->id,
                    'name'        => $squad->name,
                    'memberCount' => (int) $squad->members_count,
                    'leader'      => MemberCard::from($squad->leader),
                ])->values(),
            ])->values(),
            'anniversaries' => $this->divisionAnniversaries->map(fn ($anniversary) => [
                'name'           => $anniversary->name,
                'clanId'         => $anniversary->clan_id,
                'rankAbbr'       => $anniversary->rank?->getAbbreviation(),
                'years'          => $anniversary->years_since_joined,
                'hasTenureAward' => $anniversary->has_tenure_award ?? true,
                'trophy'         => getAnniversaryTrophy($anniversary->years_since_joined),
            ])->values(),
            'pendingActions' => $this->pendingActions->divisionActions()->map(fn (PendingAction $action) => [
                'key'   => $action->key,
                'count' => $action->count,
                'url'   => $action->url,
                'icon'  => $action->icon,
                'label' => $action->label,
                'style' => $action->style,
            ])->values(),
            'recentActivityCount' => $user->isRole('member')
                ? 0
                : $this->recentActivity->sum(fn ($group) => $group['events']->count()),
            'recentActivity' => $user->isRole('member')
                ? []
                : $this->recentActivity->map(function (array $group) {
                    $type  = $group['type'];
                    $count = $group['events']->count();

                    return [
                        'icon'        => $type->feedIconName(),
                        'tone'        => $type->feedTone(),
                        'description' => $type->feedDescription($count),
                        'count'       => $count,
                        'timeAgo'     => $group['created_at']->diffForHumans(short: true),
                        'targets'     => $group['events']->map(fn ($event) => $event->subject
                            ? ['name' => $event->subject->name, 'url' => route('member', $event->subject->getUrlParams())]
                            : ['name' => 'Unknown', 'url' => null])->values(),
                    ];
                })->values(),
            'canViewAllActivity' => $user->isRole(['sr_ldr', 'admin']),
            'allActivityUrl'     => route('filament.mod.resources.activities.index'),
            'organize'           => [
                'canOrganize' => $user->can('manageUnassigned', User::class),
                'members'     => $user->can('manageUnassigned', User::class)
                    ? $division->unassigned()->get()
                        ->map(fn (Member $member) => [
                            'id'   => $member->id,
                            'name' => $member->present()->rankName(),
                        ])->values()
                    : [],
            ],
            'pendingApplicationCount' => $this->pendingApplicationCount,
        ];
    }
}
