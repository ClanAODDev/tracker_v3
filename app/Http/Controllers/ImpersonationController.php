<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Impersonate a given user
     *
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function impersonate(User $user)
    {
        $this->middleware(['auth', 'admin']);
        
        if ($user->isDeveloper()) {
            abort(403, 'You cannot impersonate that user');
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
        if (session('impersonating') && session('impersonatingUser')) {
            Auth::login(User::find(session('impersonatingUser')));
            session()->forget(['impersonating', 'impersonatingUser']);
            $this->showToast('Impersonation ended');
        }

        return redirect()->back();
    }
}
