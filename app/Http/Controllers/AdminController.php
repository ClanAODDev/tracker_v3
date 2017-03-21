<?php

namespace App\Http\Controllers;

use App\Handle;
use App\User;
use Charts;
use App\Division;
use Illuminate\Http\Request;
use App\Repositories\ClanRepository;

use App\Http\Requests;
use App\Http\Controllers\Controller;
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
        dd($division);
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
