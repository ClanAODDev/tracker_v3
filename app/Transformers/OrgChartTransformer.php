<?php

namespace App\Transformers;

use App\Enums\Position;
use App\Models\Division;
use App\Models\Member;
use App\Models\Unit;
use Illuminate\Support\Collection;

class OrgChartTransformer
{
    private Division $division;

    private Collection $unitsByParent;

    public function transform(Division $division, $leaders, Collection $units): array
    {
        $this->division      = $division;
        $this->unitsByParent = $units->groupBy(fn (Unit $unit) => $unit->parent_id ?? 0);

        $children = [];

        if ($leaders->isNotEmpty()) {
            $children[] = $this->transformLeadershipGroup($leaders);
        }

        foreach ($this->unitsByParent->get(0, collect()) as $unit) {
            $children[] = $this->transformUnit($unit);
        }

        return [
            'id'       => "division-{$division->id}",
            'name'     => $division->name,
            'type'     => 'division',
            'logo'     => $division->getLogoPath(),
            'children' => $children,
        ];
    }

    private function transformLeadershipGroup($leaders): array
    {
        $children = [];

        foreach ($leaders as $leader) {
            $type       = $leader->position === Position::COMMANDING_OFFICER ? 'co' : 'xo';
            $children[] = $this->transformMember($leader, $type);
        }

        return [
            'id'       => 'leaders',
            'name'     => 'Leadership',
            'type'     => 'leadership-group',
            'children' => $children,
        ];
    }

    private function transformUnit(Unit $unit): array
    {
        $childUnits = $this->unitsByParent->get($unit->id, collect());

        if ($unit->isSquad() || ($childUnits->isEmpty() && ! $unit->isTopLevel())) {
            return $this->transformSquad($unit);
        }

        $node = [
            'id'          => "platoon-{$unit->id}",
            'name'        => $unit->name,
            'description' => $unit->description,
            'type'        => 'platoon',
            'logo'        => $unit->logo ? $unit->getLogoPath() : null,
            'children'    => $childUnits->map(fn (Unit $child) => $this->transformUnit($child))->all(),
        ];

        if ($unit->leader) {
            $node['leader'] = $this->transformLeaderInfo($unit->leader);
        }

        return $node;
    }

    private function transformSquad(Unit $squad): array
    {
        $members = $squad->members
            ->filter(fn ($m) => $m->clan_id !== $squad->leader_id)
            ->sortByDesc('rank')
            ->sortBy('name')
            ->values();

        $children = [];
        foreach ($members as $member) {
            $children[] = $this->transformMember($member, 'member');
        }

        $node = [
            'id'       => "squad-{$squad->id}",
            'name'     => $squad->name,
            'type'     => 'squad',
            'children' => $children,
        ];

        if ($squad->leader) {
            $node['leader'] = $this->transformLeaderInfo($squad->leader);
        }

        return $node;
    }

    private function transformMember(Member $member, string $type): array
    {
        return [
            'id'        => "member-{$member->clan_id}",
            'clanId'    => $member->clan_id,
            'name'      => $member->name,
            'rankName'  => $member->present()->rankName(),
            'rankColor' => $member->rank->getColorHex(),
            'handle'    => $this->getMemberHandle($member),
            'type'      => $type,
        ];
    }

    private function transformLeaderInfo(Member $leader): array
    {
        return [
            'clanId'    => $leader->clan_id,
            'name'      => $leader->name,
            'rankName'  => $leader->present()->rankName(),
            'rankColor' => $leader->rank->getColorHex(),
            'handle'    => $this->getMemberHandle($leader),
        ];
    }

    private function getMemberHandle(Member $member): ?string
    {
        return $this->division->handleSummaryFor($member);
    }
}
