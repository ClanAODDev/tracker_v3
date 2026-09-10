<?php

namespace App\Http\Controllers;

use App\Data\UnitStatsData;
use App\Enums\Position;
use App\Models\Division;
use App\Models\Platoon;
use App\Repositories\PlatoonRepository;
use App\Services\MemberQueryService;
use App\Support\MemberListProps;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class PlatoonController extends Controller
{
    public function __construct(
        private PlatoonRepository $platoon,
        private MemberQueryService $memberQuery,
    ) {}

    public function show(Division $division, Platoon $platoon): Response
    {
        $platoon->load('squads.leader', 'squads.members', 'unassigned');

        $members            = $this->memberQuery->loadSortedMembers($platoon->members(), $division);
        $voiceActivityGraph = $this->platoon->getPlatoonVoiceActivity($platoon);
        $unitStats          = UnitStatsData::fromMembers($members, $division, $voiceActivityGraph);

        $activityThreshold = now()->subDays($division->settings()->get('inactivity_days') ?? 30);
        $canManage         = auth()->user()->can('update', $platoon);

        $unassigned = $platoon->unassigned
            ->filter(fn ($member) => $member->position === Position::MEMBER)
            ->values();

        return Inertia::render('division/members', [
            ...MemberListProps::build($division, $members, $unitStats, assignmentKind: 'squad'),
            'scope' => [
                'kind'            => 'platoon',
                'name'            => $platoon->name ?: 'Untitled ' . $division->locality('platoon'),
                'platoonLabel'    => $division->locality('platoon'),
                'squadLabel'      => $division->locality('squad'),
                'logo'            => $platoon->getLogoPath(),
                'canManage'       => $canManage,
                'editUrl'         => $canManage ? route('filament.mod.resources.platoons.edit', $platoon) : null,
                'manageUrl'       => $canManage ? route('platoon.manage-squads', [$division->slug, $platoon]) : null,
                'unassignedCount' => $unassigned->count(),
                'breadcrumbs'     => [
                    ['label' => $division->name, 'href' => route('division', $division->slug)],
                    ['label' => $platoon->name ?: 'Untitled'],
                ],
            ],
            'squads' => $platoon->squads->map(function ($squad, $i) use ($division, $platoon, $activityThreshold) {
                $count  = $squad->members->count();
                $active = $squad->members->filter(fn ($m) => $m->last_voice_activity >= $activityThreshold)->count();

                return [
                    'id'        => $squad->id,
                    'name'      => $squad->name ?: ordSuffix($i + 1) . ' Squad',
                    'url'       => route('squad.show', [$division->slug, $platoon, $squad]),
                    'leader'    => $squad->leader?->present()->rankName(),
                    'count'     => $count,
                    'voiceRate' => $count > 0 ? round(($active / $count) * 100) : 0,
                ];
            })->values(),
            'organize' => [
                'canOrganize' => $canManage,
                'members'     => $canManage
                    ? $unassigned->map(fn ($member) => [
                        'id'   => $member->id,
                        'name' => $member->present()->rankName(),
                    ])->values()
                    : [],
            ],
        ]);
    }

    public function manageSquads(Division $division, Platoon $platoon): Response
    {
        $this->authorize('update', $platoon);

        $platoon->load('squads.members', 'squads.leader', 'unassigned');

        $squads = $platoon->squads->map(function ($squad) {
            $members = $squad->members
                ->filter(fn ($member) => $member->position === Position::MEMBER)
                ->sortByDesc(fn ($member) => $squad->leader && $squad->leader->clan_id === $member->recruiter_id)
                ->map(fn ($member) => [
                    'id'              => $member->id,
                    'name'            => $member->present()->rankName(),
                    'isDirectRecruit' => $squad->leader && $squad->leader->clan_id === $member->recruiter_id,
                ])
                ->values();

            return [
                'id'      => $squad->id,
                'name'    => $squad->name ?: 'Untitled',
                'leader'  => $squad->leader?->present()->rankName(),
                'members' => $members,
            ];
        })->values();

        $unassigned = $platoon->unassigned
            ->filter(fn ($member) => $member->position === Position::MEMBER)
            ->map(fn ($member) => ['id' => $member->id, 'name' => $member->present()->rankName()])
            ->values();

        return Inertia::render('platoon/manage-members', [
            'division' => [
                'name'         => $division->name,
                'squadLabel'   => $division->locality('Squad'),
                'squadPlural'  => Str::plural($division->locality('squad')),
                'platoonLabel' => $division->locality('platoon'),
            ],
            'platoon'        => ['id' => $platoon->id, 'name' => $platoon->name],
            'squads'         => $squads,
            'unassigned'     => $unassigned,
            'assignUrl'      => url('/members/assign-squad'),
            'backUrl'        => route('platoon', [$division->slug, $platoon]),
            'createSquadUrl' => route('filament.mod.resources.platoons.edit', $platoon),
        ]);
    }
}
