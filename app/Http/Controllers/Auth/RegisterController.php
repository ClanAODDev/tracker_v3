<?php

namespace App\Http\Controllers\Auth;

use App\Mail\WelcomeEmail;
use App\User;
use Illuminate\Support\Facades\Mail;
use Validator;
use App\Member;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest');
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
                'unique:users'
            ],
            'member_id' => 'unique:users',
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

        Mail::to($data['email'])->send(new WelcomeEmail());

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'member_id' => $member->id,
        ]);
    }
}
