<?php

namespace App\Http\Controllers\Auth;

use App\AOD\ClanForumPermissions;
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
        return Member::whereName($name)->first() ?? false;
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

                (new ClanForumPermissions())->handleAccountRoles(
                    $user->member->clan_id,
                    $this->roles
                );

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
}
