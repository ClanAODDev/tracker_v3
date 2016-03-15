<?php

namespace App\Http\Controllers;

use App\User;
use App\Member;
use Illuminate\Http\Request;

use App\Http\Requests;

class APIController extends Controller
{

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function users()
    {
        return User::all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function members()
    {
        return Member::all();
    }
}
