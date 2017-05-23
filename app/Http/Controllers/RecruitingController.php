<?php

namespace App\Http\Controllers;

use App\Division;
use App\Handle;
use App\Member;
use App\Notifications\NewMemberRecruited;
use App\Platoon;
use App\Squad;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Whossun\Toastr\Toastr;

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

    /**
     * @param Request $request
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function stepTwo(Request $request, Division $division)
    {
        // @TODO: Verify platoon, squad are in current division

        $rules = [
            'member_id' => 'required|digits_between:1,5',
            'forum_name' => 'required',
            'ingame_name' => 'required'
        ];

        $messages = [
            'member_id.digits_between' => "Forum Member ID appears to be invalid."
        ];

        $this->validate($request, $rules, $messages);

        // allow for training mode recruitments
        $isTesting = $this->isTestUser($request['forum_name'], $request['member_id']);

        return view('recruit.step-two', compact('request', 'division', 'isTesting'));
    }

    /**
     * Test mpde functionality
     *
     * @param $name
     * @param $id
     * @return bool
     */
    private function isTestUser($name, $id)
    {
        // @TODO: Define test user in environment
        $test_user = [
            'name' => 'test-user',
            'id' => 99999
        ];

        return $name === $test_user['name'] && $id == $test_user['id'];
    }

    /**
     * @param Request $request
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function stepThree(Request $request, Division $division)
    {
        $isTesting = $request['is_testing'];

        if ( ! $isTesting) {
            // create or update member record
            $member = $this->createMember($request, $division);

            // notify slack of recruitment
            if ($division->settings()->get('slack_alert_created_member')) {
                $division->notify(new NewMemberRecruited($member, $division));
            }

            $this->showToast("Member #{$request['member_id']} added to the tracker!");
        }

        return view('recruit.step-three', compact('division', 'isTesting', 'request'));
    }

    /**
     * Handle member creation on recruitment
     *
     * @param $request
     * @param $division
     * @return
     */
    private function createMember($request, $division)
    {
        $member = Member::firstOrNew(['clan_id' => $request->member_id]);

        $member->name = $request->forum_name;
        $member->join_date = Carbon::today();
        $member->last_activity = Carbon::today();
        $member->recruiter_id = auth()->user()->member->clan_id;
        $member->rank_id = 1;
        $member->position_id = 1;
        $member->save();

        // assign to division
        $member->divisions()->sync([
            $division->id => [
                'primary' => true
            ]
        ]);

        // handle ingame name assignment
        if ($division->handle) {
            $member->handles()->syncWithoutDetaching([
                Handle::find($division->handle_id)->id => [
                    'value' => $request->ingame_name
                ]
            ]);
        } else {
            $this->showErrorToast('Your division does not have a default ingame handle, so the ingame name could not be stored');
        }

        $member->platoon()->associate(Platoon::find($request->platoon));
        $member->squad()->associate(Squad::find($request->squad));

        $member->recordActivity('recruited');


        return $member;
    }

    /**
     * @param Request $request
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function stepFour(Request $request, Division $division)
    {
        return view('recruit.step-four', compact('division', 'request'));
    }

    public function stepFive(Division $division, Request $request)
    {
        return view('recruit.step-five', compact('division'));
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

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function doThreadCheck(Request $request)
    {
        $division = Division::find($request->division);
        $threads = $division->settings()->get('recruiting_threads');
        $isTesting = $request['isTesting'];

        foreach ($threads as $key => $thread) {
            $threads[$key]['url'] = doForumFunction([$threads[$key]['thread_id']], 'showThread');
            $threads[$key]['status'] = ($request->isTesting)
                ? true
                : $division->threadCheck($request['string'], $threads[$key]['url']);
            sleep(2);
        }

        return view('recruit.partials.thread-check', compact('division', 'threads', 'isTesting'));
    }
}
