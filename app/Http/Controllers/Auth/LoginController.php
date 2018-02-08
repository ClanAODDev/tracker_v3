<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class LoginController extends Controller
{

    use ThrottlesLogins;
    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
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

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    private function attemptLogin($request)
    {
        if (app()->environment() === 'local') {
            Auth::login(User::whereName('Guybrush')->first());
        }

        if ($this->validatesAODCredentials($request)) {
            $username = str_replace('aod_', '', strtolower($request->username));
            if ($user = User::whereName($username)->first()) {
                Auth::login($user);

                return true;
            }
        }

        return false;
    }

    /**
     * Authenticates with AOD Community Service
     *
     * @param $request
     * @return bool
     */
    private function validatesAODCredentials($request)
    {
        $password = md5($request->password);
        $query = "CALL check_user('{$request->username}', '{$password}')";
        $results = DB::connection('aod_forums')->select($query);

        if ( ! empty($results)) {
            // TODO: should store group information eventually
            // returns integer
            return (array_first($results)->valid);
        }

        return false;
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
