<?php

namespace App\Http\Controllers;

use App\Http\Requests\Squad\AssignSquadMemberRequest;
use App\Models\Member;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
class SquadController extends Controller
{
    public function assignMember(AssignSquadMemberRequest $request, UnitAssignment $units): JsonResponse
    {
        $member = Member::findOrFail($request->member_id);

        if ((int) $request->unit_id === 0) {
            $current = $member->unit;

            if (! $current) {
                return response()->json(['success' => true]);
            }

            $this->authorize('update', $current->parent ?? $current);

            $units->assignMember($member, null);

            return response()->json(['success' => true]);
        }

        $unit = Unit::query()->findOrFail($request->unit_id);
        abort_unless($unit->division_id === $member->division_id, 422);
        $this->authorize('update', $unit);

        $units->assignMember($member, $unit);

        return response()->json(['success' => true]);
    }
}
