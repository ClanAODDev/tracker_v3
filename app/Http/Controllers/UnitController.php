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
                'squadLabel'   => $this->childLabel($unit),
                'squadPlural'  => Str::plural($this->childLabel($unit)),
                'platoonLabel' => $unit->levelLabel(),
            ],
            'platoon'        => ['id' => $unit->id, 'name' => $unit->name],
            'squads'         => $squads,
            'unassigned'     => $unassigned,
            'assignUrl'      => url('/members/assign-squad'),
            'backUrl'        => $unit->url($division),
            'breadcrumbs'    => $this->ancestry($division, $unit, linkSelf: true),
            'createSquadUrl' => route('filament.mod.resources.units.edit', $unit->id),
        ]);
    }

    private function showPlatoon(Division $division, Unit $platoon): Response
    {
        $platoon->load('children.leader');

        $members            = $this->memberQuery->loadSortedMembers($platoon->allMembers(), $division);
        $voiceActivityGraph = $this->units->getVoiceActivity($platoon);
        $unitStats          = UnitStatsData::fromMembers($members, $division, $voiceActivityGraph);

        $activityThreshold = now()->subDays($division->settings()->get('inactivity_days') ?? 30);
        $canManage         = auth()->user()->can('update', $platoon);
        $unassigned        = $this->unassigned($division, $platoon);
        $unitPaths         = Unit::query()->where('path', 'like', $platoon->path . '%')->pluck('path', 'id');
        $childLabel        = $this->childLabel($platoon);

        return Inertia::render('division/members', [
            ...MemberListProps::build($division, $members, $unitStats, assignmentKind: 'squad'),
            'scope' => [
                'kind'            => 'platoon',
                'name'            => $platoon->name ?: 'Untitled ' . $platoon->levelLabel(),
                'platoonLabel'    => $platoon->levelLabel(),
                'squadLabel'      => $childLabel,
                'logo'            => $platoon->getLogoPath(),
                'canManage'       => $canManage,
                'editUrl'         => $canManage ? route('filament.mod.resources.units.edit', $platoon->id) : null,
                'manageUrl'       => $canManage ? route('unit.manage', [$division->slug, $platoon]) : null,
                'unassignedCount' => $unassigned->count(),
                'breadcrumbs'     => $this->ancestry($division, $platoon),
            ],
            'squads' => $platoon->children->map(function (Unit $child, $i) use ($division, $members, $unitPaths, $activityThreshold, $childLabel) {
                $descendants = $members->filter(fn ($member) => isset($unitPaths[$member->unit_id])
                    && str_starts_with($unitPaths[$member->unit_id], $child->path));
                $count  = $descendants->count();
                $active = $descendants->filter(fn ($m) => $m->last_voice_activity >= $activityThreshold)->count();

                return [
                    'id'        => $child->id,
                    'name'      => $child->name ?: ordSuffix($i + 1) . ' ' . $childLabel,
                    'url'       => $child->url($division),
                    'leader'    => $child->leader?->present()->rankName(),
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
        $parent = $squad->parent;
        $parent?->load('children.leader');
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
                'name'        => $squad->name ?: 'Untitled ' . $squad->levelLabel(),
                'squadLabel'  => $squad->levelLabel(),
                'canManage'   => $canManage,
                'editUrl'     => $canManage ? route('filament.mod.resources.units.edit', $squad->id) : null,
                'breadcrumbs' => $this->ancestry($division, $squad),
            ],
            'squads' => ($parent?->children ?? collect())->map(fn (Unit $sibling, $i) => [
                'name'    => $sibling->name ?: ordSuffix($i + 1) . ' ' . $sibling->levelLabel(),
                'url'     => $sibling->url($division),
                'leader'  => $sibling->leader?->present()->rankName(),
                'current' => $sibling->id === $squad->id,
            ])->values(),
        ]);
    }

    private function childLabel(Unit $unit): string
    {
        return $unit->childLevel()?->label ?? 'Unit';
    }

    private function ancestry(Division $division, Unit $unit, bool $linkSelf = false): array
    {
        $trail = [['label' => $unit->name ?: 'Untitled', 'href' => $linkSelf ? $unit->url($division) : null]];

        for ($ancestor = $unit->parent; $ancestor !== null; $ancestor = $ancestor->parent) {
            array_unshift($trail, ['label' => $ancestor->name ?: 'Untitled', 'href' => $ancestor->url($division)]);
        }

        array_unshift($trail, ['label' => $division->name, 'href' => route('division', $division->slug)]);

        return array_map(fn (array $crumb) => array_filter($crumb, fn ($value) => $value !== null), $trail);
    }

    private function unassigned(Division $division, Unit $platoon)
    {
        return $platoon->members()
            ->where('division_id', $division->id)
            ->where('position', Position::MEMBER)
            ->get();
    }
}
