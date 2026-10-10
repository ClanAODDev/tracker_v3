<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Models\Division;
use App\Models\Member;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Validation\Rule;

#[Authorize('manageUnassigned', User::class)]
class BulkMoveController extends Controller
{
    public function getPlatoons(Division $division): JsonResponse
    {
        $byParent = $division->units()
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->groupBy('parent_id');

        $build = function (?int $parentId) use (&$build, $byParent, $division) {
            return ($byParent->get($parentId) ?? collect())->map(fn (Unit $unit) => [
                'id'         => $unit->id,
                'name'       => $unit->name ?: 'Untitled',
                'levelLabel' => $division->unitLevel($unit->depth)?->label ?? 'Unit',
                'children'   => $build($unit->id),
            ])->values();
        };

        return response()->json(['units' => $build(null)]);
    }

    public function store(Request $request, Division $division): JsonResponse
    {
        $validated = $request->validate([
            'member_ids'   => 'required|array',
            'member_ids.*' => 'integer',
            'unit_id'      => ['required', 'integer', Rule::exists('units', 'id')->where('division_id', $division->id)->whereNull('deleted_at')],
        ]);

        $unit = Unit::query()->findOrFail($validated['unit_id']);

        $members = Member::whereIn('clan_id', $validated['member_ids'])
            ->where('division_id', $division->id)
            ->get();

        foreach ($members as $member) {
            $member->update(['unit_id' => $unit->id]);

            if ($unit->parent === null) {
                $member->recordActivity(ActivityType::ASSIGNED_PLATOON, ['platoon' => $unit->name]);
            } else {
                $member->recordActivity(ActivityType::ASSIGNED_SQUAD, [
                    'platoon' => $unit->parent->name,
                    'squad'   => $unit->name,
                ]);
            }
        }

        $count = $members->count();

        return response()->json([
            'success' => true,
            'message' => $count . ' ' . ($count === 1 ? 'member' : 'members') . ' transferred to ' . ($unit->name ?: 'Untitled'),
        ]);
    }
}
