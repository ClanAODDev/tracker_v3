<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Member;
use App\User;
use Auth;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    use ThrottlesLogins, AuthenticatesWithAOD;
    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    protected $decayMinutes = 5;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * @param $username
     * @return bool
     */
    private function registerNewUser($username)
    {
        if ($member = $this->isCurrentAODMember($username)) {

            $user = \App\User::updateOrCreate([
                'email' => $this->email,
                'name' => $username,
                'member_id' => $member->id
            ]);

            Auth::login($user);

            return true;
        }

        return false;
    }

    /**
     * @param $name
     * @return bool
     */
    private function isCurrentAODMember($name)
    {
        return Member::whereForumName($name)->first() ?? false;
    }

    /**
     * @param Request $request
     * @return Factory|RedirectResponse|Response|View
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse();
    }

    private function attemptLogin($request)
    {
        if ($this->validatesCredentials($request)) {
            $username = str_replace('aod_', '', strtolower($request->username));

            if ($user = User::whereName($username)->first()) {
                $this->checkForUpdates($user);
                Auth::login($user);

                return true;
            }

            if ($this->registerNewUser($username)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update user's email if updated
     *
     * @param $user
     */
    private function checkForUpdates($user)
    {
        if ($user->email !== $this->email) {
            $user->update(['email' => $this->email]);
        }
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $this->handleAccountRoles();

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been authenticated.
     *
     * @param Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    protected function redirectPath()
    {
        return $this->redirectTo;
    }

    /**
     * Get the failed login response instance.
     *
     * @return Factory|View
     */
    protected function sendFailedLoginResponse()
    {
        return view('auth.login')->withErrors([
            'login' => 'Invalid login credentials'
        ]);
    }

    /**
     * @return array|Request|string
     */
    public function username()
    {
        return request('username');
    }

    /**
     * @return Factory|View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return response()->redirectTo('/');
    }

    /**
     * Provision account role based on forum groups
     */
    private function handleAccountRoles()
    {
        $roles = \Illuminate\Support\Arr::flatten($this->roles->toArray());

        $officerRoleIds = \DB::table('divisions')->select('officer_role_id')
            ->where('active', true)
            ->where('officer_role_id', '!=', null)
            ->pluck('officer_role_id')->toArray();

        /**
         * Update role unless current role matches new role
         */
        switch (true) {
            case array_intersect($roles, ['Banned Users', 49]):
                return (auth()->user()->role_id != 6) ? $this->assignRole('banned') : null;
            case array_intersect($roles, ['Administrators', 6]):
                return (auth()->user()->role_id != 5) ? $this->assignRole('admin') : null;
            case array_intersect($roles, ['AOD Sergeants', 52, 'AOD Staff Sergeants', 66]):
                return (auth()->user()->role_id != 4) ? $this->assignRole('sr_ldr') : null;
            case array_intersect($roles, $officerRoleIds):
                return (auth()->user()->role_id != 2) ? $this->assignRole('officer') : null;
            default:
                return (auth()->user()->role_id != 1) ? $this->assignRole('member') : null;
        }
    }

    /**
     * @param string $role
     */
    private function assignRole(string $role)
    {
        \Log::info("Role {$role} granted to user " . auth()->id());
        auth()->user()->assignRole($role);
    }
}
