<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Impersonate a given user
     *
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function impersonate(User $user)
    {
        if ( ! $this->canImpersonate($user)) {
            abort(403);
        }

        session(['impersonating' => true]);
        session(['impersonatingUser' => auth()->user()->id]);

        $user->activity('start_impersonation');

        $this->showToast('You are now impersonating ' . $user->name);

        Auth::login($user);

        return redirect('/');
    }

    /**
     * @param $user
     * @return bool
     */
    private function canImpersonate($user)
    {
        // only admins can impersonate
        if ( ! auth()->user()->isRole('admin')) {
            return false;
        }

        // can't impersonate developers
        if ($user->isDeveloper()) {
            return false;
        }

        return true;
    }

    /**
     * End an impersonation
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function endImpersonation()
    {
        if (session('impersonating') && session('impersonatingUser')) {
            $user = User::find(session('impersonatingUser'));
            Auth::login($user);
            session()->forget(['impersonating', 'impersonatingUser']);
            $user->activity('stop_impersonating');
            $this->showToast('Impersonation ended');
        }

        return redirect()->back();
    }
}
