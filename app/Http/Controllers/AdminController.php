<?php

namespace App\Http\Controllers;

use App\Division;
use Illuminate\Http\Request;

use Log;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function index()
    {
        $divisions = Division::all();

        return view('admin.index', compact('divisions'));
    }

    public function updateDivisions(Request $request)
    {
        $updates = collect($request->input('divisions'));

        foreach ($updates as $abbreviation => $status) {

            $division = Division::whereAbbreviation($abbreviation)->firstOrFail();

            // only perform an update if the statuses differ
            if ((bool)$division->active != (bool)$status) {
                $division->active = (bool)$status;
                $division->save();
            }

        }

        return redirect()->back();
    }
}
