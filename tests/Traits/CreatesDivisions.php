<?php

namespace Tests\Traits;

use App\Models\Division;
use App\Models\Member;
use App\Models\Unit;

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

    protected function createDivisionWithHandles(array $handles, array $attributes = []): Division
    {
        return Division::factory()->withHandles(...$handles)->create(array_merge([
            'active' => true,
        ], $attributes));
    }

    protected function createInactiveDivision(array $attributes = []): Division
    {
        return Division::factory()->create(array_merge([
            'active'      => false,
            'shutdown_at' => now(),
        ], $attributes));
    }

    protected function createPlatoon(?Division $division = null, array $attributes = []): Unit
    {
        $division = $division ?? $this->createActiveDivision();

        return Unit::factory()->create(array_merge([
            'division_id' => $division->id,
        ], $attributes));
    }

    protected function createSquad(?Unit $platoon = null, array $attributes = []): Unit
    {
        $platoon = $platoon ?? $this->createPlatoon();

        return Unit::factory()->childOf($platoon)->create($attributes);
    }

    protected function createDivisionWithPlatoons(int $platoonCount = 2, array $divisionAttributes = []): Division
    {
        $division = $this->createActiveDivision($divisionAttributes);

        Unit::factory()->count($platoonCount)->create([
            'division_id' => $division->id,
        ]);

        return $division->fresh(['topUnits']);
    }

    protected function createDivisionWithFullStructure(
        int $platoonCount = 2,
        int $squadsPerPlatoon = 2,
        int $membersPerSquad = 3,
        array $divisionAttributes = []
    ): Division {
        $division = $this->createActiveDivision($divisionAttributes);

        for ($p = 0; $p < $platoonCount; $p++) {
            $platoon = Unit::factory()->create([
                'division_id' => $division->id,
                'order'       => ($p + 1) * 100,
            ]);

            for ($s = 0; $s < $squadsPerPlatoon; $s++) {
                $squad = Unit::factory()->childOf($platoon)->create();

                Member::factory()->count($membersPerSquad)->create([
                    'division_id' => $division->id,
                    'unit_id'     => $squad->id,
                ]);
            }
        }

        return $division->fresh(['topUnits.children.members']);
    }

    protected function createPlatoonWithSquads(?Division $division = null, int $squadCount = 3, array $platoonAttributes = []): Unit
    {
        $division = $division ?? $this->createActiveDivision();

        $platoon = Unit::factory()->create(array_merge([
            'division_id' => $division->id,
        ], $platoonAttributes));

        Unit::factory()->childOf($platoon)->count($squadCount)->create();

        return $platoon->fresh(['children']);
    }

    protected function createSquadWithMembers(?Unit $platoon = null, int $memberCount = 5, array $squadAttributes = []): Unit
    {
        $platoon = $platoon ?? $this->createPlatoon();

        $squad = Unit::factory()->childOf($platoon)->create($squadAttributes);

        Member::factory()->count($memberCount)->create([
            'division_id' => $platoon->division_id,
            'unit_id'     => $squad->id,
        ]);

        return $squad->fresh(['members']);
    }
}
