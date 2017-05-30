<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function updateRole(Request $request)
    {
        $user = User::find($request->user);
        $this->authorize('update', [User::class, $request->role]);
        $user->assignRole(Role::find($request->role));
    }

}
