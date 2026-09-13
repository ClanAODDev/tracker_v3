<?php

namespace App\Http\Controllers;

use App\Models\Division;
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
        ]);
    }

    public function data(Division $division): JsonResponse
    {
        return response()->json($this->buildTree($division));
    }

    private function buildTree(Division $division): array
    {
        $handleFilter = $this->filterHandlesToPrimaryHandle($division);

        $division->load([
            'platoons.leader.handles'         => $handleFilter,
            'platoons.squads.leader.handles'  => $handleFilter,
            'platoons.squads.members.handles' => $handleFilter,
        ]);

        $leaders = $division->leaders()
            ->with(['handles' => $handleFilter])
            ->orderByDesc('position')
            ->orderByDesc('rank')
            ->get();

        return (new OrgChartTransformer)->transform($division, $leaders);
    }

    private function filterHandlesToPrimaryHandle(Division $division): Closure
    {
        return fn ($query) => $query->where('handles.id', $division->handle_id);
    }
}
