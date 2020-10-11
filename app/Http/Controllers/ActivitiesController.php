<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\User;

class ActivitiesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function byUser(User $user)
    {
        $division = $user->member->division;

        $activity = $user->activity()->with([
            'subject' => function ($query) {
                // provide context even if a subject is "trashed"
                $query->withTrashed();
            }
        ])->get()->reverse();

        return view('activity.show', compact('activity', 'division', 'user'));
    }

    public function byDivision(Division $division)
    {
        $activity = $division->activity()->with([
            'subject' => function ($query) {
                // provide context even if a subject is "trashed"
                $query->withTrashed();
            }
        ])->orderByDesc('created_at')->get();

        return view('activity.show', compact('activity', 'division'));
    }
}
