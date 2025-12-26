<?php

namespace Tests\Traits;

use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;

trait CreatesDivisions
{
    protected function createDivision(array $attributes = []): Division
    {
        return Division::factory()->create($attributes);
    }

    protected function createActiveDivision(array $attributes = []): Division
    {
        return Division::factory()->create(array_merge([
            'active' => true,
        ], $attributes));
    }

    protected function createInactiveDivision(array $attributes = []): Division
    {
        return Division::factory()->create(array_merge([
            'active' => false,
            'shutdown_at' => now(),
        ], $attributes));
    }

    protected function createPlatoon(?Division $division = null, array $attributes = []): Platoon
    {
        $division = $division ?? $this->createActiveDivision();

        return Platoon::factory()->create(array_merge([
            'division_id' => $division->id,
        ], $attributes));
    }

    protected function createSquad(?Platoon $platoon = null, array $attributes = []): Squad
    {
        $platoon = $platoon ?? $this->createPlatoon();

        return Squad::factory()->create(array_merge([
            'platoon_id' => $platoon->id,
        ], $attributes));
    }

    protected function createDivisionWithPlatoons(int $platoonCount = 2, array $divisionAttributes = []): Division
    {
        $division = $this->createActiveDivision($divisionAttributes);

        Platoon::factory()->count($platoonCount)->create([
            'division_id' => $division->id,
        ]);

        return $division->fresh(['platoons']);
    }

    protected function createDivisionWithFullStructure(
        int $platoonCount = 2,
        int $squadsPerPlatoon = 2,
        int $membersPerSquad = 3,
        array $divisionAttributes = []
    ): Division {
        $division = $this->createActiveDivision($divisionAttributes);

        for ($p = 0; $p < $platoonCount; $p++) {
            $platoon = Platoon::factory()->create([
                'division_id' => $division->id,
                'order' => ($p + 1) * 100,
            ]);

            for ($s = 0; $s < $squadsPerPlatoon; $s++) {
                $squad = Squad::factory()->create([
                    'platoon_id' => $platoon->id,
                ]);

                Member::factory()->count($membersPerSquad)->create([
                    'division_id' => $division->id,
                    'platoon_id' => $platoon->id,
                    'squad_id' => $squad->id,
                ]);
            }
        }

        return $division->fresh(['platoons.squads.members']);
    }

    protected function createPlatoonWithSquads(?Division $division = null, int $squadCount = 3, array $platoonAttributes = []): Platoon
    {
        $division = $division ?? $this->createActiveDivision();

        $platoon = Platoon::factory()->create(array_merge([
            'division_id' => $division->id,
        ], $platoonAttributes));

        Squad::factory()->count($squadCount)->create([
            'platoon_id' => $platoon->id,
        ]);

        return $platoon->fresh(['squads']);
    }

    protected function createSquadWithMembers(?Platoon $platoon = null, int $memberCount = 5, array $squadAttributes = []): Squad
    {
        $platoon = $platoon ?? $this->createPlatoon();

        $squad = Squad::factory()->create(array_merge([
            'platoon_id' => $platoon->id,
        ], $squadAttributes));

        Member::factory()->count($memberCount)->create([
            'division_id' => $platoon->division_id,
            'platoon_id' => $platoon->id,
            'squad_id' => $squad->id,
        ]);

        return $squad->fresh(['members']);
    }
}
