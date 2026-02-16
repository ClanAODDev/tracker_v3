<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BulkMoveController extends Controller
{
    public function getPlatoons(Division $division): JsonResponse
    {
        $this->authorize('manageUnassigned', User::class);

        $platoons = $division->platoons()
            ->with('squads:id,platoon_id,name')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($platoon) => [
                'id'     => $platoon->id,
                'name'   => $platoon->name ?? 'Untitled',
                'squads' => $platoon->squads->map(fn ($squad) => [
                    'id'   => $squad->id,
                    'name' => $squad->name ?? 'Untitled',
                ]),
            ]);

        return response()->json(['platoons' => $platoons]);
    }

    public function store(Request $request, Division $division): JsonResponse
    {
        $this->authorize('manageUnassigned', User::class);

        $validated = $request->validate([
            'member_ids'   => 'required|array',
            'member_ids.*' => 'integer',
            'platoon_id'   => 'required|integer|exists:platoons,id',
            'squad_id'     => 'nullable|integer',
        ]);

        $platoon = Platoon::where('id', $validated['platoon_id'])
            ->where('division_id', $division->id)
            ->firstOrFail();

        $squad = null;
        if (! empty($validated['squad_id'])) {
            $squad = Squad::where('id', $validated['squad_id'])
                ->where('platoon_id', $platoon->id)
                ->first();
        }

        $members = Member::whereIn('clan_id', $validated['member_ids'])
            ->where('division_id', $division->id)
            ->get();

        $transferredCount = 0;
        foreach ($members as $member) {
            $member->platoon_id = $platoon->id;
            $member->squad_id   = $squad ? $squad->id : 0;
            $member->save();

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
