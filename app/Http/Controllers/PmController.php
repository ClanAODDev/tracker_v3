<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendBulkPm;
use Spatie\ValidationRules\Rules\Delimited;

use Illuminate\Http\Request;

class PmController extends Controller
{
    public function create(SendBulkPm $request)
    {
        $validated = $request->validated();

        return view('division.create-pm')->with([
            'members' => collect(explode(',', $validated['pm-member-data'])),
            'division' => $request->division
        ]);
    }
}
