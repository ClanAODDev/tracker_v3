<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Transformers\OrgChartTransformer;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('auth')]
class DivisionOrgChartController extends Controller
{
    public function show(Division $division): View
    {
        return view('division.org-chart', compact('division'));
    }

    public function data(Division $division): JsonResponse
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

        $transformer = new OrgChartTransformer;

        return response()->json($transformer->transform($division, $leaders));
    }

    private function filterHandlesToPrimaryHandle(Division $division): Closure
    {
        return fn ($query) => $query
            ->where('handles.id', $division->handle_id);
    }
}
