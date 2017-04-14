<?php

namespace App\Http\Controllers;

use App\Division;
use App\Handle;
use App\Repositories\ClanRepository;
use App\User;
use Charts;
use Illuminate\Http\Request;
use Whossun\Toastr\Facades\Toastr;

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

        $handles = Handle::with('divisions')->get();

        $users = User::with('role', 'member', 'member.rank')->get();

        return view('admin.index', compact('divisions', 'users', 'handles'));
    }

    public function editDivision(Division $division)
    {
        $this->authorize('show', $division);

        return view('admin.modify-division', compact('division'));
    }

    public function editHandle(Handle $handle)
    {
        dd($handle);
    }


    public function updateDivisions(Request $request)
    {
        $updates = collect($request->input('divisions'));
        $changeCount = 0;

        foreach ($updates as $abbreviation => $status) {
            $division = Division::whereAbbreviation($abbreviation)->firstOrFail();

            // only perform an update if the statuses differ
            if ((bool) $division->active != (bool) $status) {
                $changeCount++;
                $division->active = (bool) $status;
                $division->save();
            }
        }

        if ( ! $changeCount) {
            Toastr::warning('No changes made', "Update divisions", [
                'positionClass' => 'toast-top-right',
                'progressBar' => true
            ]);

            return redirect()->back();
        }

        Toastr::success("{$changeCount} divisions were updated successfully!",
            "Update Divisions", [
                'positionClass' => 'toast-top-right',
                'progressBar' => true
            ]
        );


        return redirect()->back();
    }
}
