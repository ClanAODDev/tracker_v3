<?php

namespace App\Http\Controllers;

use App\Division;
use App\Repositories\ClanRepository;
use ConsoleTVs\Charts\Charts;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function __construct(ClanRepository $clanRepository)
    {
        $this->clanRepository = $clanRepository;

        $this->middleware('auth', 'admin');
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

        return Charts::create('area', 'morris')
            ->setLabels($data['labels'])
            ->setValues($data['values'])
            ->setElementLabel('Rank count')
            ->setResponsive(true);
    }

    public function updateDivisions(Request $request)
    {
        $updates = collect($request->input('divisions'));
        $changes = 0;

        foreach ($updates as $abbreviation => $status) {
            $division = Division::whereAbbreviation($abbreviation)->firstOrFail();

            // only perform an update if the statuses differ
            if ((bool)$division->active != (bool)$status) {
                $changes++;
                $division->active = (bool)$status;
                $division->save();
            }
        }

        if ( ! $changes) {
            flash('No changes were made.', 'info');

            return redirect()->back();
        }

        flash("{$changes} divisions were updated successfully!", 'success');

        return redirect()->back();
    }
}
