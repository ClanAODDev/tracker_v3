<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Http\Requests\Squad\AssignSquadMemberRequest;
use App\Models\Member;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
class SquadController extends Controller
{
    public function assignMember(AssignSquadMemberRequest $request): JsonResponse
    {
        $member = Member::findOrFail($request->member_id);

        if ((int) $request->unit_id === 0) {
            $current = $member->unit;

            if (! $current) {
                return response()->json(['success' => true]);
            }

            $this->authorize('update', $current->parent ?? $current);

            $member->update(['unit_id' => null]);
            $member->recordActivity(ActivityType::UNASSIGNED);

            return response()->json(['success' => true]);
        }

        $unit = Unit::query()->findOrFail($request->unit_id);
        abort_unless($unit->division_id === $member->division_id, 422);
        $this->authorize('update', $unit);

        $member->update(['unit_id' => $unit->id]);

        if ($unit->parent === null) {
            $member->recordActivity(ActivityType::ASSIGNED_PLATOON, ['platoon' => $unit->name]);
        } else {
            $member->recordActivity(ActivityType::ASSIGNED_SQUAD, [
                'platoon' => $unit->parent->name,
                'squad'   => $unit->name,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
