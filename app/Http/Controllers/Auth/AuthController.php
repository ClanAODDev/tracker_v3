<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Member;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Validator;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    
    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * flash success on login
     */
    public function authenticated(Request $request, \App\User $user)
    {
        flash()->success("Welcome back {$user->name}!");
        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {

        $messages = [
            'regex' => 'Username cannot contain "AOD_"',
            'exists' => 'AOD member name not found',
        ];

        return Validator::make($data, [
            // ensure member exists before registering user
            'name' => [
                'Regex:/^((?!AOD_|aod_).)*$/',
                'required',
                'max:255',
                'exists:members',
            ],
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ], $messages);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return User
     */
    protected function create(array $data)
    {
        /**
         * All users must have a corresponding member entry
         */
        $member = Member::where('name', $data['name'])->first();

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'member_id' => $member->id,
        ]);
    }
}
