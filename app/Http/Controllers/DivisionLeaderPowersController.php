<?php

namespace App\Http\Controllers;

use App\Enums\UnitLeaderPower;
use App\Enums\UnitLevel;
use App\Models\Division;
use App\Models\DivisionUnitLevel;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class DivisionLeaderPowersController extends Controller
{
    private const EXAMPLE_DEPTHS = [1, 2, 4];

    public function __invoke(Division $division): Response
    {
        $deepest = $division->deepestUnitLevel();

        return Inertia::render('division/leader-powers', [
            'division' => ['name' => $division->name, 'slug' => $division->slug],
            'levels'   => $division->unitLevels->map(fn (DivisionUnitLevel $level) => [
                'depth'  => $level->depth,
                'label'  => $level->label,
                'title'  => $level->leader_title,
                'tier'   => $this->tier($level->depth, $deepest),
                'powers' => UnitLeaderPower::forDivision($division, $level->depth),
            ])->values(),
            'examples' => collect(self::EXAMPLE_DEPTHS)->map(fn (int $levels) => [
                'levels' => collect(range(1, $levels))->map(fn (int $depth) => [
                    'depth' => $depth,
                    'label' => "Level {$depth}",
                    'tier'  => $this->tier($depth, $levels),
                ])->all(),
            ])->all(),
        ]);
    }

    private function tier(int $depth, int $deepest): string
    {
        return strtolower(UnitLevel::forDepth($depth, $deepest)->name);
    }
}
