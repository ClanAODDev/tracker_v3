<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Http\Requests\Squad\AssignSquadMemberRequest;
use App\Models\Member;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
class SquadController extends Controller
{
    public function __construct(private UnitAssignment $units) {}

    public function assignMember(AssignSquadMemberRequest $request): JsonResponse
    {
        $member = Member::findOrFail($request->member_id);

        if ((int) $request->unit_id === 0) {
            $platoon = $member->platoonUnit();

            if (! $platoon) {
                return response()->json(['success' => true]);
            }

            $this->authorize('update', $platoon);

            $member->update($this->units->columnsFor(null));
            $member->recordActivity(ActivityType::UNASSIGNED);

            return response()->json(['success' => true]);
        }

        $squad = Unit::query()->where('legacy_type', Unit::LEGACY_SQUAD)->findOrFail($request->unit_id);
        abort_if($squad->parent === null, 404);
        $this->authorize('update', $squad->parent);

        $member->update($this->units->columnsFor($squad));
        $member->recordActivity(ActivityType::ASSIGNED_SQUAD, [
            'platoon' => $squad->parent->name,
            'squad'   => $squad->name,
        ]);

        return response()->json(['success' => true]);
    }
}
