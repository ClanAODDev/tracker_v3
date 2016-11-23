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
                // provide context even if a subject is "trashed"
                $query->withTrashed();
            }
        ])->get();

        return view('activity.show', compact('activity', 'division', 'user'));
    }

    public function byDivision(Division $division)
    {
        $activity = $division->activity()->with([
            'user',
            'division',
            'subject' => function ($query) {
                // provide context even if a subject is "trashed"
                $query->withTrashed();
            }
        ])->get();

        return view('activity.show', compact('activity', 'division', 'user'));
    }
}
