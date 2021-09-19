<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendBulkPm;
use App\Models\Member;

class PmController extends Controller
{
    public function create(SendBulkPm $request)
    {
        $validated = $request->validated();

        $membersSelected = Member::whereIn('clan_id', explode(',', $validated['pm-member-data']))
            ->select('clan_id', 'allow_pm', 'name')
            ->get();

        $availableForPm = $membersSelected->filter(function ($member) {
            return ($member->allow_pm);
        });

        return view('division.create-pm')->with([
            'members' => $availableForPm,
            'selected' => $membersSelected,
            'division' => $request->division,
        ]);
    }
}
