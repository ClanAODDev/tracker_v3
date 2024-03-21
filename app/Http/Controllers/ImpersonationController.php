<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Impersonate a given user.
     *
     * @return Redirector|RedirectResponse
     */
    public function impersonate(User $user)
    {
        if (! $this->canImpersonate($user)) {
            abort(403);
        }

        session(['impersonating' => true]);
        session(['impersonatingUser' => auth()->user()->id]);

        auth()->user()->recordActivity('start_impersonation', $user->member);

        $this->showToast('You are now impersonating ' . $user->name);

        Auth::login($user);

        return redirect('/');
    }

    /**
     * End an impersonation.
     *
     * @return Redirector|RedirectResponse
     */
    public function endImpersonation()
    {
        if (session('impersonating') && session('impersonatingUser')) {
            $user = User::find(session('impersonatingUser'));
            // need to log end of impersonation
            Auth::login($user);
            session()->forget(['impersonating', 'impersonatingUser']);
            $this->showToast('Impersonation ended');
        }

        return redirect()->back();
    }

    /**
     * @return bool
     */
    private function canImpersonate($user)
    {
        // only admins can impersonate
        if (! auth()->user()->isRole('admin')) {
            return false;
        }

        // can't impersonate developers
        if ($user->isDeveloper()) {
            return false;
        }

        return true;
    }
}
