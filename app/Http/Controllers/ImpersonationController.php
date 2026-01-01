<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    use AuthorizesRequests;

    public function impersonate(User $user): Redirector|RedirectResponse
    {
        $this->authorize('impersonate', $user);

        session(['impersonating' => true]);
        session(['impersonatingUser' => auth()->user()->id]);

        $this->showSuccessToast('You are now impersonating ' . $user->name);

        Auth::login($user);

        return redirect('/');
    }

    public function endImpersonation(): Redirector|RedirectResponse
    {
        if (session('impersonating') && session('impersonatingUser')) {
            $user = User::find(session('impersonatingUser'));
            Auth::login($user);
            session()->forget(['impersonating', 'impersonatingUser']);
            $this->showSuccessToast('Impersonation ended');
        }

        return redirect()->back();
    }

    public function impersonateRole(string $role): RedirectResponse
    {
        if (! auth()->user()->isRole('admin') && ! auth()->user()->isDeveloper()) {
            abort(403);
        }

        $roleEnum = Role::fromSlug($role);

        if (! $roleEnum) {
            $this->showErrorToast('Invalid role');

            return redirect()->back();
        }

        session(['impersonatingRole' => $roleEnum->value]);
        session(['originalRole' => auth()->user()->role->value]);

        $this->showSuccessToast('Now viewing as ' . $roleEnum->getLabel());

        return redirect()->back();
    }

    public function endRoleImpersonation(): RedirectResponse
    {
        session()->forget(['impersonatingRole', 'originalRole']);
        $this->showSuccessToast('Role impersonation ended');

        return redirect()->back();
    }
}
