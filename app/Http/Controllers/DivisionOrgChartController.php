<?php

namespace App\Http\Controllers;

use App\Enums\Position;
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
        $handleFilter = $this->filterHandlesToDivisionHandles($division);

        $units = $division->units()
            ->with([
                'division.unitLevels',
                'leader.handles'  => $handleFilter,
                'members.handles' => $handleFilter,
            ])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $leaders = $division->leaders()
            ->with(['handles' => $handleFilter])
            ->orderByDesc('position')
            ->orderByDesc('rank')
            ->get();

        $roster = $division->isFlat()
            ? $division->members()
                ->whereNotIn('position', [Position::COMMANDING_OFFICER, Position::EXECUTIVE_OFFICER])
                ->with(['handles' => $handleFilter])
                ->get()
            : null;

        return (new OrgChartTransformer)->transform($division, $leaders, $units, $roster);
    }

    private function filterHandlesToDivisionHandles(Division $division): Closure
    {
        return fn ($query) => $query->whereIn('handles.id', $division->handles->pluck('id'));
    }
}
