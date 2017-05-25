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
    public function form(Division $division)
    {
        return view('recruit.form', compact('division'));
    }

    /**
     * @param Request $request
     * @param Division $division
     */
    public function submitRecruitment(Request $request, Division $division)
    {
        if ( ! ($request->inDemoMode)) {
            // create or update member record
            $member = $this->createMember($request, $division);

            // notify slack of recruitment
            if ($division->settings()->get('slack_alert_created_member')) {
                $division->notify(new NewMemberRecruited($member, $division));
            }
        }
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
     * @param $abbreviation
     * @return array
     */
    public function searchPlatoons($abbreviation)
    {
        $division = Division::whereAbbreviation($abbreviation)->first();

        return $this->getPlatoonsAndSquadsFor($division);
    }

    /**
     * @param $division
     * @return array
     */
    private function getPlatoonsAndSquadsFor($division)
    {
        return [
            'data' => [
                'platoons' => $division->platoons->pluck('name', 'id'),
                'squads' => $division->squads->pluck('name', 'id')
            ]
        ];
    }

    /**
     * ajax method
     *
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
        return $platoon->squads->load('leader');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function doThreadCheck(Request $request)
    {
        $division = Division::whereAbbreviation($request->division)->first();
        $threads = $division->settings()->get('recruiting_threads');
        $isTesting = $request->isTesting;

        foreach ($threads as $key => $thread) {
            $threads[$key]['url'] = doForumFunction([$threads[$key]['thread_id']], 'showThread');
            $threads[$key]['status'] = ($request->isTesting)
                ? true
                : $division->threadCheck($request['string'], $threads[$key]['url']);
            sleep(2);
        }

        return $threads;
    }
}
