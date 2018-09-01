<?php

namespace App\Http\Controllers;

use App\Division;
use App\Handle;
use App\Member;
use App\MemberRequest;
use App\Notifications\NewExternalRecruit;
use App\Notifications\NewMemberRecruited;
use App\Platoon;
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
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $this->authorize('create', Member::class);

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
        $this->authorize('create', Member::class);

        return view('recruit.form', compact('division'));
    }

    /**
     * @param Request $request
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function submitRecruitment(Request $request)
    {
        $this->authorize('create', Member::class);

        $division = Division::whereAbbreviation($request->division)->first();

        // create or update member record
        $member = $this->createMember($request);

        // request member status
        $this->createRequest($member, $division);

        // notify slack of recruitment
        if ($division->settings()->get('slack_alert_created_member') == "on") {
            $this->handleNotification($request, $member, $division);
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

        // update member properties
        $member->name = $request->forum_name;
        $member->join_date = Carbon::today();
        $member->last_activity = Carbon::today();
        $member->recruiter_id = auth()->user()->member->clan_id;
        $member->rank_id = $request->rank;
        $member->position_id = 1;
        $member->division_id = $division->id;
        $member->save();

        // handle ingame name assignment
        if ($request->ingame_name) {
            $member->handles()->syncWithoutDetaching([
                Handle::find($division->handle_id)->id => [
                    'value' => $request->ingame_name
                ]
            ]);
        }

        // handle assignments
        $member->platoon_id = $request->platoon;
        $member->squad_id = $request->squad;

        $member->save();

        $member->recordActivity('recruited');

        return $member;
    }

    /**
     * Create a member status request
     *
     * @param $member
     * @param $division
     */
    private function createRequest($member, $division)
    {
        MemberRequest::create([
            'requester_id' => auth()->user()->member->clan_id,
            'member_id' => $member->clan_id,
            'division_id' => $division->id,
        ]);
    }

    /**
     * @param Request $request
     * @param $member
     * @param $division
     */
    private function handleNotification(Request $request, $member, $division)
    {
        if ($division != auth()->user()->member->division) {
            return $division->notify(new NewExternalRecruit($member, $division));
        }

        return $division->notify(new NewMemberRecruited($member, $division));
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

    /**
     * @param $memberId
     * @return array
     */
    public function validateMember($memberId)
    {
        if (app()->environment() === 'local') {
            if ($memberId == 31832) {
                return ['isMember' => true];
            }

            return ['isMember' => false];
        }

        $result = \DB::connection('aod_forums')
            ->select("CALL get_user({$memberId})");

        return ['isMember' => ! empty($result)];
    }
}
