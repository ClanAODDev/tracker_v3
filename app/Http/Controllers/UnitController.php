<?php

namespace App\Http\Controllers;

use App\Data\UnitStatsData;
use App\Enums\Position;
use App\Models\Division;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use App\Services\MemberQueryService;
use App\Support\MemberListProps;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class UnitController extends Controller
{
    public function __construct(
        private UnitRepository $units,
        private MemberQueryService $memberQuery,
    ) {}

    public function show(Division $division, Unit $unit): Response
    {
        return $unit->isSquad() ? $this->showSquad($division, $unit) : $this->showPlatoon($division, $unit);
    }

    public function manage(Division $division, Unit $unit): Response
    {
        abort_unless($unit->isPlatoon(), 404);
        $this->authorize('update', $unit);

        $unit->load([
            'children.members' => fn ($query) => $query->where('division_id', $division->id),
            'children.leader',
        ]);

        $squads = $unit->children->map(function (Unit $squad) {
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

        $unassigned = $this->unassigned($division, $unit)
            ->map(fn ($member) => ['id' => $member->id, 'name' => $member->present()->rankName()])
            ->values();

        return Inertia::render('platoon/manage-members', [
            'division' => [
                'name'         => $division->name,
                'squadLabel'   => $division->locality('Squad'),
                'squadPlural'  => Str::plural($division->locality('squad')),
                'platoonLabel' => $division->locality('platoon'),
            ],
            'platoon'        => ['id' => $unit->id, 'name' => $unit->name],
            'squads'         => $squads,
            'unassigned'     => $unassigned,
            'assignUrl'      => url('/members/assign-squad'),
            'backUrl'        => $unit->url($division),
            'createSquadUrl' => route('filament.mod.resources.platoons.edit', $unit->id),
        ]);
    }

    private function showPlatoon(Division $division, Unit $platoon): Response
    {
        $platoon->load([
            'children.leader',
            'children.members' => fn ($query) => $query->where('division_id', $division->id),
        ]);

        $members            = $this->memberQuery->loadSortedMembers($platoon->allMembers(), $division);
        $voiceActivityGraph = $this->units->getVoiceActivity($platoon);
        $unitStats          = UnitStatsData::fromMembers($members, $division, $voiceActivityGraph);

        $activityThreshold = now()->subDays($division->settings()->get('inactivity_days') ?? 30);
        $canManage         = auth()->user()->can('update', $platoon);
        $unassigned        = $this->unassigned($division, $platoon);

        return Inertia::render('division/members', [
            ...MemberListProps::build($division, $members, $unitStats, assignmentKind: 'squad'),
            'scope' => [
                'kind'            => 'platoon',
                'name'            => $platoon->name ?: 'Untitled ' . $division->locality('platoon'),
                'platoonLabel'    => $division->locality('platoon'),
                'squadLabel'      => $division->locality('squad'),
                'logo'            => $platoon->getLogoPath(),
                'canManage'       => $canManage,
                'editUrl'         => $canManage ? route('filament.mod.resources.platoons.edit', $platoon->id) : null,
                'manageUrl'       => $canManage ? route('unit.manage', [$division->slug, $platoon]) : null,
                'unassignedCount' => $unassigned->count(),
                'breadcrumbs'     => [
                    ['label' => $division->name, 'href' => route('division', $division->slug)],
                    ['label' => $platoon->name ?: 'Untitled'],
                ],
            ],
            'squads' => $platoon->children->map(function (Unit $squad, $i) use ($division, $activityThreshold) {
                $count  = $squad->members->count();
                $active = $squad->members->filter(fn ($m) => $m->last_voice_activity >= $activityThreshold)->count();

                return [
                    'id'        => $squad->id,
                    'name'      => $squad->name ?: ordSuffix($i + 1) . ' Squad',
                    'url'       => $squad->url($division),
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

    private function showSquad(Division $division, Unit $squad): Response
    {
        $platoon = $squad->parent;
        $platoon?->load('children.leader');
        $squad->loadMissing('leader');

        $members            = $this->memberQuery->loadSortedMembers($squad->members(), $division);
        $voiceActivityGraph = $this->units->getVoiceActivity($squad);
        $unitStats          = UnitStatsData::fromMembers($members, $division, $voiceActivityGraph);
        $canManage          = auth()->user()->can('update', $squad);

        return Inertia::render('division/members', [
            ...MemberListProps::build(
                $division,
                $members,
                $unitStats,
                assignmentKind: 'squad',
                directRecruitOfClanId: $squad->leader?->clan_id,
            ),
            'scope' => [
                'kind'        => 'squad',
                'name'        => $squad->name ?: 'Untitled ' . $division->locality('squad'),
                'canManage'   => $canManage,
                'editUrl'     => $canManage ? route('filament.mod.resources.squads.edit', $squad->id) : null,
                'breadcrumbs' => array_values(array_filter([
                    ['label' => $division->name, 'href' => route('division', $division->slug)],
                    $platoon ? ['label' => $platoon->name ?: 'Untitled', 'href' => $platoon->url($division)] : null,
                    ['label' => $squad->name ?: 'Untitled'],
                ])),
            ],
            'squads' => ($platoon?->children ?? collect())->map(fn (Unit $sibling, $i) => [
                'name'    => $sibling->name ?: ordSuffix($i + 1) . ' Squad',
                'url'     => $sibling->url($division),
                'leader'  => $sibling->leader?->present()->rankName(),
                'current' => $sibling->id === $squad->id,
            ])->values(),
        ]);
    }

    private function unassigned(Division $division, Unit $platoon)
    {
        return $platoon->members()
            ->where('division_id', $division->id)
            ->where('position', Position::MEMBER)
            ->get();
    }
}
