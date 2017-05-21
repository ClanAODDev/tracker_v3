<?php

namespace App\Http\Controllers;

use App\Division;
use App\Platoon;
use Illuminate\Http\Request;

/**
 * Class RecruitingController
 *
 * @package App\Http\Controllers
 */
class RecruitingController extends Controller
{

    /**
     * RecruitingController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $divisions = Division::active()->get()->pluck('name', 'abbreviation');

        return view('recruit.index', compact('divisions'));
    }

    /**
     * @param Division $division
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function stepOne(Division $division, Request $request)
    {
        return view('recruit.step-one', compact('division'));
    }

    public function stepTwo(Request $request, Division $division)
    {
        // @TODO: Verify platoon, squad are in current division

        $rules = [
            'member-id' => 'required|digits_between:1,5',
            'forum-name' => 'required',
            'ingame-name' => 'required'
        ];

        $messages = [
            'member-id.digits_between' => "Forum Member ID appears to be invalid."
        ];

        $this->validate($request, $rules, $messages);

        return view('recruit.step-two', compact('request', 'division'));
    }

    /**
     * @param Request $request
     * @return object
     */
    public function searchPlatoonForSquads(Request $request)
    {
        return $this->getSquadsFor(Platoon::find($request->platoon));
    }

    /**
     * @param Platoon $platoon
     * @return object
     */
    public function getSquadsFor(Platoon $platoon)
    {
        return $platoon->squads->pluck('id', 'name');
    }

    public function doThreadCheck(Request $request)
    {

        $division = Division::find($request->division);
        $threads = $division->settings()->get('recruiting_threads');

        foreach ($threads as $key => $thread) {
            $threads[$key]['url'] = doForumFunction([$threads[$key]['thread_id']], 'showThread');
            $threads[$key]['status'] = ($request['forum-name'] === 'test-user')
                ? true
                : $division->threadCheck($request['string'], $threads[$key]['url']);
            sleep(2);
        }

        return view('recruit.partials.thread-check', compact('division', 'threads'));
    }
}
