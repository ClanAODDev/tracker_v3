<?php

namespace App\Http\Controllers;

use App\Enums\UnitLeaderPower;
use App\Models\Division;
use App\Models\DivisionUnitLevel;
use App\Transformers\OrgChartTransformer;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class DivisionOrgChartController extends Controller
{
    public function show(Division $division): Response
    {
        return Inertia::render('division/org-chart', [
            'division' => ['name' => $division->name, 'slug' => $division->slug],
            'tree'     => $this->buildTree($division),
            'powers'   => $this->leaderPowers($division),
        ]);
    }

    public function data(Division $division): JsonResponse
    {
        return response()->json($this->buildTree($division));
    }

    private function buildTree(Division $division): array
    {
        $handleFilter = $this->filterHandlesToDivisionHandles($division);

        $division->load([
            'topUnits.leader.handles'           => $handleFilter,
            'topUnits.children.leader.handles'  => $handleFilter,
            'topUnits.children.members.handles' => $handleFilter,
        ]);

        $leaders = $division->leaders()
            ->with(['handles' => $handleFilter])
            ->orderByDesc('position')
            ->orderByDesc('rank')
            ->get();

        return (new OrgChartTransformer)->transform($division, $leaders);
    }

    private function leaderPowers(Division $division): array
    {
        return $division->unitLevels
            ->mapWithKeys(fn (DivisionUnitLevel $level) => [
                $level->depth => [
                    'label'  => $level->label,
                    'title'  => $level->leader_title,
                    'powers' => UnitLeaderPower::forDivision($division, $level->depth),
                ],
            ])
            ->all();
    }

    private function filterHandlesToDivisionHandles(Division $division): Closure
    {
        return fn ($query) => $query->whereIn('handles.id', $division->handles->pluck('id'));
    }
}
