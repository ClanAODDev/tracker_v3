<?php

namespace App\Http\Controllers\Tools;

use App\Fireteam;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laracasts\Flash\Flash;

class FireteamController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param null $type
     * @return Fireteam|\Illuminate\Http\Response
     */
    public function index($type = null)
    {
        $fireteams = Fireteam::latest()->with([
            'players',
            'owner',
            'owner.rank',
            'players.rank'
        ]);

        if ($type) {
            $fireteams = $fireteams->whereType($type);
        }

        return view('fireteam.index', ['fireteams' => $fireteams->get()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Fireteam::create([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
            'owner_id' => auth()->user()->member_id,
            'owner_light' => $request->light,
            'starts_at' => date('Y-m-d H:i:s'),
        ]);

        $this->showToast('Your fireteam has been created!');

        return redirect(route('fireteams.index'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Fireteam $fireteam
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, Fireteam $fireteam)
    {
        $player = auth()->user()->member;

        if ($fireteam->players->contains($player)) {
            return redirect()->back()->withErrors([
                'You are already a member of that fireteam!'
            ]);
        }

        if ($fireteam->owner_id === $player->id) {
            return redirect()->back()->withErrors([
                'You cannot join your own fireteam!'
            ]);
        }

        // handle event when fireteam is full
        if ($fireteam->players_needed == $fireteam->players()->count()) {
            dump('full!');
        }


        $fireteam->players()->attach($player, ['light' => $request->light]);

        $this->showToast('You have successfully joined the fireteam!');

        return redirect()->route('fireteams.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Fireteam $fireteam
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Fireteam $fireteam)
    {
        if ($fireteam->owner_id != auth()->user()->member_id) {
            return redirect()->back()->withErrors([
                'You do not have permission to do that!'
            ]);
        }

        $fireteam->delete();
        $this->showToast('Your fireteam has been cancelled!');

        return redirect()->route('fireteams.index');
    }

    public function leave(Fireteam $fireteam)
    {
        if ( ! $fireteam->players->contains(auth()->user()->member_id)) {
            return redirect()->back()->withErrors([
                'You cannot leave a fireteam you are not a member of!'
            ]);
        }

        $fireteam->players()->detach(auth()->user()->member_id);
        $this->showToast('You have left the fireteam!');

        return redirect()->route('fireteams.index');
    }

    public function show()
    {
    }
}
