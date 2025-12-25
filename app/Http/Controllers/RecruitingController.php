<?php

namespace App\Http\Controllers;

use App\AOD\Traits\Procedureable;
use App\Enums\Position;
use App\Jobs\SyncDiscordMember;
use App\Models\Division;
use App\Models\Handle;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Platoon;
use App\Models\RankAction;
use App\Models\Transfer;
use App\Notifications\Channel\NotifyDivisionNewExternalRecruit;
use App\Notifications\Channel\NotifyDivisionNewMemberRecruited;
use DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class RecruitingController.
 */
class RecruitingController extends Controller
{
    use AuthorizesRequests;
    use Procedureable;

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
        $this->authorize('recruit', Member::class);

        $divisions = Division::active()->where('shutdown_at', null)->get();

        return view('recruit.index', compact('divisions'));
    }

    /**
     * @throws AuthorizationException
     */
    public function submitRecruitment(Request $request)
    {
        $this->authorize('recruit', Member::class);

        $division = Division::whereSlug($request->division)->first();

        $member = $this->createMember($request);

        $this->createRequest($member, $division);

        $this->handleNotification($request, $member, $division);

        SyncDiscordMember::dispatch($member);

        $this->showSuccessToast('Your recruitment has successfully been completed!');
    }

    public function form(Division $division)
    {

        $this->authorize('recruit', Member::class);
        if ($division->isShutdown()) {
            $this->showErrorToast('This division has been shutdown and cannot receive new members');

            return redirect()->back();
        }

        return view('recruit.form', compact('division'));
    }

    public function getDivisionRecruitData(Division $division): JsonResponse
    {
        $this->authorize('recruit', Member::class);

        $settings = $division->settings();
        $threads = $settings->get('recruiting_threads', []);
        $tasks = $settings->get('recruiting_tasks', []);

        return response()->json([
            'platoons' => $division->platoons->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'squads' => $p->squads->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                ]),
            ]),
            'threads' => collect($threads)->map(fn ($t) => [
                'name' => $t['thread_name'] ?? '',
                'id' => $t['thread_id'] ?? '',
                'comments' => $t['comments'] ?? '',
                'read' => false,
            ]),
            'tasks' => collect($tasks)->map(fn ($t) => [
                'description' => $t['task_description'] ?? '',
                'complete' => false,
            ]),
            'welcome_area' => $settings->get('welcome_area', ''),
            'welcome_pm' => $settings->get('welcome_pm', ''),
            'use_welcome_thread' => $settings->get('use_welcome_thread', false),
            'locality' => [
                'platoon' => $division->locality('platoon'),
                'squad' => $division->locality('squad'),
            ],
        ]);
    }

    /**
     * @return array
     */
    public function searchPlatoons($slug)
    {
        $division = Division::whereSlug($slug)->first();

        return $this->getPlatoons($division);
    }

    /**
     * Fetch a division's recruitment tasks.
     *
     * @return mixed
     */
    public function getTasks(Request $request)
    {

        $division = Division::whereSlug($request->division)->first();

        $tasks = $division->settings()->get('recruiting_tasks');

        return collect($tasks)->map(fn ($task) => ['complete' => false, 'description' => $task['task_description']]);
    }

    /**
     * ajax method.
     *
     * @return object
     */
    public function searchPlatoonForSquads(Request $request)
    {
        return $this->getSquadsFor(Platoon::find($request->platoon));
    }

    /**
     * @return object
     */
    public function getSquadsFor(Platoon $platoon)
    {
        return $platoon->squads->load('leader', 'members');
    }

    /**
     * @return Factory|View
     */
    public function doThreadCheck(Request $request)
    {
        $division = Division::whereSlug($request->division)->first();

        return $division->settings()->get('recruiting_threads');
    }

    /**
     * @return array
     */
    public function validateMemberId($member_id)
    {
        $existsInTracker = Member::where('clan_id', $member_id)->exists();

        if (app()->environment() === 'local') {
            return [
                'is_member' => true,
                'valid_group' => true,
                'username' => 'LocalTestUser',
                'exists_in_tracker' => $existsInTracker,
            ];
        }

        $result = $this->callProcedure('get_user', $member_id);

        if (! property_exists($result, 'usergroupid')) {
            return ['is_member' => false, 'valid_group' => false, 'exists_in_tracker' => $existsInTracker];
        }

        return [
            'is_member' => true,
            'username' => $result->username,
            'valid_group' => $result->usergroupid === Member::REGISTERED_USER,
            'exists_in_tracker' => $existsInTracker,
        ];
    }

    /**
     * @param  string  $name
     * @param  int  $memberId
     * @return JsonResponse
     */
    public function validateMemberName()
    {
        if (app()->environment() === 'local') {
            return response()->json(['memberExists' => false]);
        }

        $name = request('name');

        $memberId = request('member_id');

        $result = DB::connection('aod_forums')->select("CALL user_exists(?, {$memberId})", [$name]);

        return response()->json(['memberExists' => ! empty($result)]);
    }

    /**
     * Handle member creation on recruitment.
     */
    private function createMember($request)
    {
        $division = Division::whereSlug($request->division)->first();
        $member = Member::firstOrNew(['clan_id' => $request->member_id]);

        // update member properties
        $member->name = $request->forum_name;
        $member->join_date = now();
        $member->last_activity = now();
        $member->recruiter_id = auth()->user()->member?->clan_id ?? auth()->user()->id;
        $member->rank = $request->rank;
        $member->position = Position::MEMBER;
        $member->division_id = $division->id;
        $member->flagged_for_inactivity = false;
        $member->last_promoted_at = now();
        $member->save();

        // handle ingame name assignment
        if ($request->ingame_name) {
            $member->handles()->syncWithoutDetaching([Handle::find($division->handle_id)->id => ['value' => $request->ingame_name]]);
        }

        // handle assignments
        $member->platoon_id = $request->platoon;
        $member->squad_id = $request->squad;
        $member->save();
        $member->recordActivity('recruited');

        // track division assignment, rank change
        RankAction::create([
            'member_id' => $member->id,
            'rank' => $request->rank,
            'justification' => 'New recruit',
            'requester_id' => auth()->user()->member_id,
        ])->approveAndAccept();

        Transfer::create([
            'member_id' => $member->id,
            'division_id' => $division->id,
            'approved_at' => now(),
        ]);

        return $member;
    }

    /**
     * Create a member status request.
     */
    private function createRequest($member, $division)
    {
        // don't allow duplicate pending requests
        if (MemberRequest::pending()->whereMemberId($member->clan_id)->exists()) {
            return;
        }

        MemberRequest::create([
            'requester_id' => auth()->user()->member?->clan_id ?? auth()->user()->id,
            'member_id' => $member->clan_id,
            'division_id' => $division->id,
        ]);
    }

    private function handleNotification(Request $request, $member, $division)
    {
        if ($division->id !== auth()->user()->member->division_id) {
            return $division->notify(new NotifyDivisionNewExternalRecruit($member, auth()->user()));
        }

        return $division->notify(new NotifyDivisionNewMemberRecruited($member, auth()->user()));
    }

    /**
     * @return array
     */
    private function getPlatoons($division)
    {
        return ['data' => ['platoons' => $division->platoons->pluck('name', 'id'), 'settings' => $division->settings]];
    }
}
