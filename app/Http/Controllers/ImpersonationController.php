<?php

namespace App\Http\Controllers;

use App\Enums\Ability;
use App\Enums\Role;
use App\Models\User;
use App\Support\Impersonation;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Redirector;

class ImpersonationController extends Controller
{
    use AuthorizesRequests;

    #[Authorize('impersonate', 'user')]
    public function impersonate(User $user, #[CurrentUser] User $impersonator): Redirector|RedirectResponse
    {
        Impersonation::start($impersonator, $user);

        $this->showSuccessToast('You are now impersonating ' . $user->name);

        return redirect('/');
    }

    public function endImpersonation(): Redirector|RedirectResponse
    {
        if (Impersonation::stop()) {
            $this->showSuccessToast('Impersonation ended');
        }

        return redirect()->back();
    }

    public function impersonateRole(string $role, #[CurrentUser] User $user): RedirectResponse
    {
        if (! $user->can(Ability::ImpersonateRoles) && ! $user->isDeveloper()) {
            abort(403);
        }

        $roleEnum = Role::fromSlug($role);

        if (! $roleEnum) {
            $this->showErrorToast('Invalid role');

            return redirect()->back();
        }

        session(['impersonatingRole' => $roleEnum->value]);
        session(['originalRole' => $user->role->value]);

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
