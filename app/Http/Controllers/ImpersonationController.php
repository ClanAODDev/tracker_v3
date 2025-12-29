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
        $this->authorize('impersonate', $user);

        session(['impersonating' => true]);
        session(['impersonatingUser' => auth()->user()->id]);

        auth()->user()->recordActivity('start_impersonation', $user->member);

        $this->showSuccessToast('You are now impersonating ' . $user->name);

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
            $this->showSuccessToast('Impersonation ended');
        }

        return redirect()->back();
    }
}
