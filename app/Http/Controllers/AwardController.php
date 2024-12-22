<?php

namespace App\Http\Controllers;

use App\Models\Award;
use Illuminate\Database\Eloquent\Builder;

class AwardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $awards = Award::query();

        $awards->when(request('division'), function (Builder $query) {
            $query->whereHas('division', function (Builder $query) {
                $query->where('slug', request('division'));
            });
        });

        $awards = $awards->active()->withCount('recipients')->with('recipients', 'division')->get();

        if ($awards->count() === 0) {
            $this->showErrorToast('Selected division has no awards assigned. Showing all...');
            return redirect(route('awards.index'));
        }

        return view('awards.index')->with(compact('awards'));
    }

    public function storeRecommendation(Award $award)
    {
        // stub
    }

    public function show(Award $award)
    {
        return view('awards.show', compact('award'));
    }
}
