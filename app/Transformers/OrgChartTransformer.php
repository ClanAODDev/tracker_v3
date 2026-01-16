<?php

namespace App\Transformers;

use App\Enums\Position;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;

class OrgChartTransformer
{
    private ?int $divisionHandleId;

    public function transform(Division $division, $leaders): array
    {
        $this->divisionHandleId = $division->handle_id;

        $children = [];

        if ($leaders->isNotEmpty()) {
            $children[] = $this->transformLeadershipGroup($leaders);
        }

        foreach ($division->platoons as $platoon) {
            $children[] = $this->transformPlatoon($platoon);
        }

        return [
            'id' => "division-{$division->id}",
            'name' => $division->name,
            'type' => 'division',
            'logo' => $division->getLogoPath(),
            'children' => $children,
        ];
    }

    private function transformLeadershipGroup($leaders): array
    {
        $children = [];

        foreach ($leaders as $leader) {
            $type = $leader->position === Position::COMMANDING_OFFICER ? 'co' : 'xo';
            $children[] = $this->transformMember($leader, $type);
        }

        return [
            'id' => 'leaders',
            'name' => 'Leadership',
            'type' => 'leadership-group',
            'children' => $children,
        ];
    }

    private function transformPlatoon(Platoon $platoon): array
    {
        $children = [];

        foreach ($platoon->squads as $squad) {
            $children[] = $this->transformSquad($squad);
        }

        $node = [
            'id' => "platoon-{$platoon->id}",
            'name' => $platoon->name,
            'type' => 'platoon',
            'logo' => $platoon->logo ? $platoon->getLogoPath() : null,
            'children' => $children,
        ];

        if ($platoon->leader) {
            $node['leader'] = $this->transformLeaderInfo($platoon->leader);
        }

        return $node;
    }

    private function transformSquad(Squad $squad): array
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
            'id' => "squad-{$squad->id}",
            'name' => $squad->name,
            'type' => 'squad',
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
            'id' => "member-{$member->clan_id}",
            'clanId' => $member->clan_id,
            'name' => $member->name,
            'rankName' => $member->present()->rankName(),
            'rankColor' => $member->rank->getColorHex(),
            'handle' => $this->getMemberHandle($member),
            'type' => $type,
        ];
    }

    private function transformLeaderInfo(Member $leader): array
    {
        return [
            'clanId' => $leader->clan_id,
            'name' => $leader->name,
            'rankName' => $leader->present()->rankName(),
            'rankColor' => $leader->rank->getColorHex(),
            'handle' => $this->getMemberHandle($leader),
        ];
    }

    private function getMemberHandle(Member $member): ?string
    {
        if (! $this->divisionHandleId) {
            return null;
        }

        $handle = $member->handles->firstWhere('id', $this->divisionHandleId);

        return $handle?->pivot?->value;
    }
}
