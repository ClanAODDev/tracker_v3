<?php

namespace App\Http\Controllers;

use Spatie\ValidationRules\Rules\Delimited;

use Illuminate\Http\Request;

class PmController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'pm-member-data' => (new Delimited('numeric'))->min(2),
        ]);

        return view('division.create-pm')->with([
            'members' => collect(explode(',', $validated['pm-member-data'])),
            'division' => $request->division
        ]);
    }
}
