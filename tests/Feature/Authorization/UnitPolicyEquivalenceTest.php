<?php

namespace Tests\Feature\Authorization;

use App\Models\Platoon;
use App\Models\Squad;
use App\Services\Units\UnitAssignment;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;

class UnitPolicyEquivalenceTest extends PermissionMatrixTestCase
{
    #[Test]
    public function updating_a_unit_is_allowed_exactly_when_updating_its_platoon_or_squad_was(): void
    {
        $this->buildWorld();

        $legacy = [...array_values($this->world->platoons), ...array_values($this->world->squads)];

        foreach (PermissionWorld::ACTORS as $actor) {
            foreach ($legacy as $model) {
                $this->actAs($actor);
                $user = $this->world->users[$actor];

                $expected = $this->outcome(fn () => Gate::forUser($user)->allows('update', $model->fresh()));
                $actual   = $this->outcome(fn () => Gate::forUser($user)->allows('update', $this->unitFor($model)));

                $this->assertSame($expected, $actual, sprintf('%s updating %s %s', $actor, class_basename($model), $model->name));
            }
        }
    }

    private function unitFor(Platoon|Squad $model)
    {
        return app(UnitAssignment::class)->forLegacy($model)->fresh();
    }
}
