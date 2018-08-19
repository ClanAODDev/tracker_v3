<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Member;
use App\User;
use http\Exception;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;

class LoginController extends Controller
{

    use ThrottlesLogins, AuthenticatesWithAOD;
    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);

        try {
            $this->checkForAODSession();
        } catch (\Exception $exception) {
            dump($exception);
        }
    }

    /**
     * Check if an AOD session exists
     *
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    private function checkForAODSession()
    {
        if ( ! isset($_COOKIE['aod_sessionhash'])) {
            return false;
        }

        $data = $this->requestSessionInfo($_COOKIE['aod_sessionhash']);

        if (in_array($data->loggedin, [1, 2])) {
            $username = str_replace('aod_', '', strtolower($data->username));

            if ($user = User::whereName($username)->first()) {
                \Auth::login($user);

                return redirect()->intended();
            }

            if ($this->registerNewUser($username)) {
                return redirect()->intended();
            }
        }

    }

    /**
     * @param $aod_sessionhash
     * @return bool|null
     */
    private function requestSessionInfo($aod_sessionhash)
    {
        try {
            $results = \DB::connection('aod_forums')
                ->select("CALL check_session('{$aod_sessionhash}')");

            return $results[0];

        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param $username
     * @return bool
     */
    private function registerNewUser($username)
    {
        if ($member = $this->isCurrentAODMember()) {

            $user = new User;
            $user->name = $username;
            $user->email = $this->email;
            $user->member_id = $member->id;
            $user->save();

            \Auth::login($user);

            return true;
        }

        return false;
    }

    /**
     * @param $username
     * @return bool
     */
    private function isCurrentAODMember()
    {
        return Member::whereClanId($this->clanId)->first() ?? false;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     * @throws \Illuminate\Validation\ValidationException
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
        if (app()->environment() === 'local') {
            Auth::login(User::whereName('Guybrush')->first());

            return true;
        }

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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
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
     * @param  \Illuminate\Http\Request $request
     * @param  mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLoginForm()
    {
        // $this->checkForAODSession();
        return view('auth.login');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect('/');
    }
}
