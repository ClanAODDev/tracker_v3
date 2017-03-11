<?php

namespace App\Http\Controllers;

use Charts;
use App\Division;
use Illuminate\Http\Request;
use App\Repositories\ClanRepository;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function __construct(ClanRepository $clanRepository)
    {
        $this->clanRepository = $clanRepository;

        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $divisions = Division::all();

        $rankDemographic = $this->rankDemographic();

        return view('admin.index', compact('divisions', 'rankDemographic'));
    }

    public function rankDemographic()
    {
        $data = $this->clanRepository->allRankDemographic();

        return $data;

        /*return Charts::create('area', 'morris')
            ->labels($data['labels'])
            ->values($data['values'])
            ->elementLabel('Rank count')
            ->responsive(true);*/
    }

    public function updateDivisions(Request $request)
    {
        $updates = collect($request->input('divisions'));
        $changeCount = 0;

        foreach ($updates as $abbreviation => $status) {
            $division = Division::whereAbbreviation($abbreviation)->firstOrFail();

            // only perform an update if the statuses differ
            if ((bool)$division->active != (bool)$status) {
                $changeCount++;
                $division->active = (bool)$status;
                $division->save();
            }
        }

        if (! $changeCount) {
            flash('No changes were made.', 'info');

            return redirect()->back();
        }

        flash("{$changeCount} divisions were updated successfully!", 'success');

        return redirect()->back();
    }
}
