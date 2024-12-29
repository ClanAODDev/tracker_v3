<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\MemberAward;
use App\Rules\UniqueAwardForMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $awards = Award::query();

        $awards->when(request('division'), function (Builder $query) use ($awards) {
            $query->whereHas('division', function (Builder $query) {
                $query->where('slug', request('division'));
            });

            if ($awards->count() === 0) {
                $this->showErrorToast('Selected division has no awards assigned. Showing all...');

                return redirect(route('awards.index'));
            }
        });

        $awards = $awards->active()->withCount('recipients')->with('recipients', 'division')->get();



        return view('division.awards.index')->with(compact('awards'));
    }

    public function storeRecommendation(Request $request, Award $award)
    {
        if (! $award->allow_request) {
            return redirect()->back()->withErrors(['award' => 'Award requests are not allowed for this award.']);
        }

        $validatedData = $request->validate([
            'reason' => 'required|string|max:255',
            'member_id' => [
                'required',
                'numeric',
                'exists:members,clan_id',
                new UniqueAwardForMember($award->id),
            ],
        ]);

        MemberAward::create([
            'award_id' => $award->id,
            'member_id' => $validatedData['member_id'],
            'reason' => $validatedData['reason'],
        ]);

        $this->showSuccessToast('Your request/recommendation has been submitted successfully.');

        return redirect()->back();
    }

    public function show(Award $award)
    {
        return view('division.awards.show', compact('award'));
    }
}
