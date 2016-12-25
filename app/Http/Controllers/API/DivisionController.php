<?php

namespace App\Http\Controllers\API;

use App\Division;
use App\Http\Controllers\Controller;

class DivisionController extends Controller
{
    /**
     * Basic information about divisions
     *
     * @return mixed
     */
    public function info()
    {
        $divisions = Division::whereActive(true)
            ->with('members')
            ->get();

        return $divisions->map(function ($division) {
            return [
                'name' => $division->name,
                'abbreviation' => $division->abbreviation,
                'member_count' => $division->members()->count(),
                // 'leaders' => $division->leaders()->with('rank')->get()
            ];
        });
    }
}