<?php

namespace App\Http\Controllers;

use App\Division;
use App\Handle;
use App\Http\Requests\Admin\UpdateDivisionForm;
use App\Repositories\ClanRepository;
use App\User;
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



}
