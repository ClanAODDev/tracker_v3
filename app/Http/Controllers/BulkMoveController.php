<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Models\Division;
use App\Models\Member;
use App\Models\Unit;
use App\Models\User;
use App\Services\Units\UnitAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Validation\Rule;

#[Authorize('manageUnassigned', User::class)]
class BulkMoveController extends Controller
{
    public function getPlatoons(Division $division): JsonResponse
    {

        $platoons = $division->units()
            ->whereNull('parent_id')
            ->orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(fn (Unit $platoon) => [
                'id'     => $platoon->id,
                'name'   => $platoon->name ?? 'Untitled',
                'squads' => $platoon->descendantsWithTrail()->map(fn (Unit $squad) => [
                    'id'   => $squad->id,
                    'name' => $squad->trail,
                ]),
            ]);

        return response()->json(['platoons' => $platoons]);
    }

    public function store(Request $request, Division $division): JsonResponse
    {

        $validated = $request->validate([
            'member_ids'   => 'required|array',
            'member_ids.*' => 'integer',
            'platoon_id'   => ['required', 'integer', Rule::exists('units', 'id')->whereNull('parent_id')->whereNull('deleted_at')],
            'squad_id'     => 'nullable|integer',
        ]);

        $platoon = $division->units()
            ->whereNull('parent_id')
            ->findOrFail($validated['platoon_id']);

        $squad = empty($validated['squad_id'])
            ? null
            : $platoon->descendantsQuery()->find($validated['squad_id']);

        $members = Member::whereIn('clan_id', $validated['member_ids'])
            ->where('division_id', $division->id)
            ->get();

        $units            = app(UnitAssignment::class);
        $transferredCount = 0;
        foreach ($members as $member) {
            $member->update(['unit_id' => ($squad ?? $platoon)->id]);

            $member->recordActivity(ActivityType::ASSIGNED_PLATOON, [
                'platoon' => $platoon->name,
            ]);

            if ($squad) {
                $member->recordActivity(ActivityType::ASSIGNED_SQUAD, [
                    'squad' => $squad->name,
                ]);
            }

            $transferredCount++;
        }

        $destination = $platoon->name ?? 'Untitled';
        if ($squad) {
            $destination .= ' / ' . ($squad->name ?? 'Untitled');
        }

        return response()->json([
            'success' => true,
            'message' => $transferredCount . ' ' . ($transferredCount === 1 ? 'member' : 'members') . ' transferred to ' . $destination,
        ]);
    }
}
