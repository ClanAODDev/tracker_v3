<?php

namespace App\Http\Controllers;

use App\Division;
use App\Handle;
use App\Http\Requests\Admin\UpdateDivisionForm;
use App\Repositories\ClanRepository;
use App\User;
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

    /**
     * @param UpdateDivisionForm $form
     * @param Division $division
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDivision(UpdateDivisionForm $form, Division $division)
    {
        $form->persist();

        Toastr::success("{$division->name} Division was updated successfully!",
            "Update Division", [
                'positionClass' => 'toast-top-right',
                'progressBar' => true
            ]
        );

        return redirect(route('admin') . '#divisions');
    }
}
