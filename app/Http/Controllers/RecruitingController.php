<?php

namespace App\Http\Controllers;

use App\Division;
use App\Handle;
use App\Member;
use App\Notifications\NewMemberRecruited;
use App\Platoon;
use App\Squad;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Class RecruitingController
 *
 * @package App\Http\Controllers
 */
class RecruitingController extends Controller
{

    use AuthorizesRequests;

    /**
     * RecruitingController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->authorize('create', Member::class);
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $divisions = Division::active()
            ->get()
            ->sortBy('name')
            ->pluck('name', 'abbreviation');

        return view('recruit.index', compact('divisions'));
    }

    /**
     * @param Division $division
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function form(Division $division)
    {

        return view('recruit.form', compact('division'));
    }

    /**
     * @param Request $request
     */
    public function submitRecruitment(Request $request)
    {


        $division = Division::whereAbbreviation($request->division)->first();

        // create or update member record
        $member = $this->createMember($request);

        // notify slack of recruitment
        if ($division->settings()->get('slack_alert_created_member')) {
            $division->notify(new NewMemberRecruited($member, $division));
        }

        $this->showToast('Your recruitment has successfully been completed!');

    }

    /**
     * Handle member creation on recruitment
     *
     * @param $request
     * @return
     */
    private function createMember($request)
    {

        $division = Division::whereAbbreviation($request->division)->first();
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

        // handle assignments
        $member->platoon()->associate(Platoon::find($request->platoon));
        $member->squad()->associate(Squad::find($request->squad));
        $member->save();

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

        return $this->getPlatoons($division);
    }

    /**
     * @param $division
     * @return array
     */
    private function getPlatoons($division)
    {
        return [
            'data' => [
                'platoons' => $division->platoons->pluck('name', 'id'),
                'settings' => $division->settings
            ]
        ];
    }

    /**
     * Fetch a division's recruitment tasks
     *
     * @param Request $request
     * @return mixed
     */
    public function getTasks(Request $request)
    {
        $division = Division::whereAbbreviation($request->division)->first();
        $tasks = $division->settings()->get('recruiting_tasks');

        return collect($tasks)->map(function ($task) {
            return [
                'complete' => false,
                'description' => $task['task_description']
            ];
        });
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
        return $platoon->squads->load('leader', 'members');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function doThreadCheck(Request $request)
    {
        $division = Division::whereAbbreviation($request->division)->first();
        $threads = $division->settings()->get('recruiting_threads');

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
