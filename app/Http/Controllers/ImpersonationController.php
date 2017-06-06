<?php

namespace App\Http\Controllers;

use App\User;

class ImpersonationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Impersonate a given user
     *
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function impersonate(User $user)
    {
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
