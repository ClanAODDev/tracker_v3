<?php

namespace App\Http\Controllers;

use App\GameBan;
use App\Http\Requests;
use Auth;

class GameBanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function submitBan(Request $request)
    {
        if (Auth::user()->isRole('admin') || Auth::user()->isRole('srLeader')) {
            $ban = new GameBan;
            $ban->member_id = Auth::user()->member_id;
            $ban->bannedUser = $request['bannedUser'];
            if (!is_null($request['ip'])) {
                $ban->ip_address = $request['ip'];
            }
            if (!is_null($request['ea'])) {
                $ban->ea_guid = $request['ea'];
            }
            if (!is_null($request['pb'])) {
                $ban->pb_guid = $request['pb'];
            }
            $ban->reason = $request['reason'];

            // FIXME Guybrush, I don't know how to add a flash to the user if successful. Please add
            return view('layouts.home');
        } else {
            return view('errors.404');
        }
    }

    /*
     * Returns the view displaying all of the game bans for specified division
     */
    public function viewGameBans()
    {
        if (Auth::user()->isRole('admin') || Auth::user()->isRole('srLeader')) {
            $bans = GameBan::all();
            return view('bans.viewGameBans', compact($bans));
        } else {
            return view('errors.404');
        }
    }
}
