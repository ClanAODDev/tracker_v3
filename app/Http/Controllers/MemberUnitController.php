<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\AssignMemberToUnitRequest;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
class MemberUnitController extends Controller
{
    public function assign(AssignMemberToUnitRequest $request, UnitAssignment $units): JsonResponse
    {
        $member = $request->member() ?? abort(404);

        if ((int) $request->unit_id === 0) {
            $current = $member->unit;

            if (! $current) {
                return response()->json(['success' => true]);
            }

            $units->assignMember($member, null);

            return response()->json(['success' => true]);
        }

        $unit = Unit::query()->findOrFail($request->unit_id);
        abort_unless($unit->division_id === $member->division_id, 422);

        $units->assignMember($member, $unit);

        return response()->json(['success' => true]);
    }
}
