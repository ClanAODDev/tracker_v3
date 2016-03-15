<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Member;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

/**
 * Class APIController
 *
 * Handles primitive API requests.
 *
 * @package App\Http\Controllers\API
 */
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
