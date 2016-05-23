<?php

namespace App\Http\Controllers;

use App\Division;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class ActivitiesController extends Controller
{
    public function byUser(User $user)
    {
        $division = $user->member->primaryDivision;

        $activity = $user->activity()->with([
            'user',
            'division',
            'subject' => function ($query) {
                $query->withTrashed();
            }
        ])->get();

        return view('activity.show', compact('activity', 'division', 'user'));
    }

    public function byDivision(Division $division)
    {
        return $division->activity()->with([
            'user',
            'subject',
            'division'
        ])->get();
    }
}
