<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Member;
use App\Models\Unit;
use App\Models\User;
use App\Services\Units\UnitAssignment;
use App\Support\UnitTree;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Validation\Rule;

#[Authorize('manageUnassigned', User::class)]
class BulkMoveController extends Controller
{
    public function getPlatoons(Division $division): JsonResponse
    {
        return response()->json(['units' => UnitTree::for($division)]);
    }

    public function store(Request $request, Division $division, UnitAssignment $units): JsonResponse
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

        $members->each(fn (Member $member) => $units->assignMember($member, $unit));

        $count = $members->count();

        return response()->json([
            'success' => true,
            'message' => $count . ' ' . ($count === 1 ? 'member' : 'members') . ' transferred to ' . ($unit->name ?: 'Untitled'),
        ]);
    }
}
