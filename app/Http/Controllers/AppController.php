<?php

namespace App\Http\Controllers;

use App\Division;
use App\User;
use Auth;
use Mail;

class AppController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $myDivision = Auth::user()->member->primaryDivision;

        $activeDivisions = Division::active()->withCount('members')->orderBy('name')->get();
        $divisions = $activeDivisions->except($myDivision->id);

        return view('home.show', compact(
            'divisions',
            'myDivision'
        ));
    }

    /**
     * Impersonate a given user
     *
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function impersonate(User $user)
    {
        $this->middleware(['auth', 'admin']);

        if ($user->id === auth()->user()->id) {
            $this->showErrorToast("You can't impersonate yourself, silly!");

            return redirect('/');
        }

        session(['impersonating' => true]);
        session(['impersonatingUser' => auth()->user()->id]);
        $this->showToast('You are now impersonating ' . $user->name);

        Auth::login($user);

        return redirect('/');
    }

    /**
     * End an impersonation
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function endImpersonation()
    {
        $this->middleware(['auth', 'admin']);

        if (session('impersonating') && session('impersonatingUser')) {
            Auth::login(User::find(session('impersonatingUser')));
            session()->forget(['impersonating', 'impersonatingUser']);
            $this->showToast('Impersonation ended');
        }

        return redirect()->back();
    }
}

/* Toastr::success('You have successfully logged in!', 'Hello, ' . strtoupper(Auth::user()->name), [
        'positionClass' => 'toast-top-right',
        'progressBar' => true
    ]);*/
