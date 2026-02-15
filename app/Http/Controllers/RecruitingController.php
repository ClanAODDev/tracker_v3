<?php

namespace App\Http\Controllers;

use App\Enums\ForumGroup;
use App\Enums\Position;
use App\Jobs\SyncDiscordMember;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\Member;
use App\Models\User;
use App\Notifications\Channel\NotifyDivisionNewExternalRecruit;
use App\Notifications\Channel\NotifyDivisionNewMemberRecruited;
use App\Services\AODForumService;
use App\Services\ForumProcedureService;
use App\Services\RecruitmentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecruitingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ForumProcedureService $procedureService,
        protected RecruitmentService $recruitmentService
    ) {
        $this->middleware('auth');
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $this->authorize('recruit', Member::class);

        $divisions = Division::active()->where('shutdown_at', null)
            ->withoutFloaters()
            ->withoutBR()
            ->get();

        return view('recruit.index', compact('divisions'));
    }

    /**
     * @throws AuthorizationException
     */
    public function submitRecruitment(Request $request)
    {
        $this->authorize('recruit', Member::class);

        $division = Division::whereSlug($request->division)->first();
        $recruiterId = auth()->user()->member->clan_id;

        if ($request->pending_user_id) {
            $pendingUser = User::pendingDiscord()->find($request->pending_user_id);

            if (! $pendingUser) {
                return response()->json([
                    'message' => 'Pending Discord user not found.',
                ], 422);
            }

            $forumService = app(AODForumService::class);
            $forumUser = $forumService->getUserByEmail($pendingUser->email);

            if (! $forumUser && $pendingUser->forum_password) {
                $forumUser = $this->createLegacyForumAccount($pendingUser, $division, $forumService);
            }

            if (! $forumUser) {
                return response()->json([
                    'message' => 'Forum account not found for this user. Registration may not have completed.',
                ], 422);
            }

            $clanId = (int) $forumUser->userid;
            $forumProfile = $this->procedureService->getUser($clanId);

            if ($forumProfile && property_exists($forumProfile, 'usergroupid')) {
                $group = ForumGroup::tryFrom((int) $forumProfile->usergroupid);

                if ($group && ! $group->isEligibleForRecruitment()) {
                    return response()->json([
                        'message' => $group->recruitmentRejectionReason(),
                    ], 422);
                }
            }

            $member = $this->recruitmentService->createMember(
                $clanId,
                $request->forum_name,
                $division,
                (int) $request->rank,
                (int) $request->platoon,
                $request->squad ? (int) $request->squad : null,
                $request->ingame_name,
                $recruiterId
            );

            $this->recruitmentService->createMemberRequest($member, $division, $recruiterId);

            $pendingUser->update(['member_id' => $member->id]);

            DivisionApplication::where('user_id', $pendingUser->id)
                ->pending()
                ->get()
                ->each->delete();

            $this->handleNotification($member, $division);

            SyncDiscordMember::dispatch($member);

            $this->showSuccessToast('Recruitment completed for Discord user.');

            return;
        }

        $member = $this->recruitmentService->createMember(
            (int) $request->member_id,
            $request->forum_name,
            $division,
            (int) $request->rank,
            (int) $request->platoon,
            $request->squad ? (int) $request->squad : null,
            $request->ingame_name,
            $recruiterId
        );

        $this->recruitmentService->createMemberRequest($member, $division, $recruiterId);

        $this->handleNotification($member, $division);

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

        $platoons = $division->platoons()
            ->withCount('members')
            ->with([
                'leader:clan_id,name',
                'squads' => fn ($q) => $q->withCount('members')->with('leader:clan_id,name'),
            ])
            ->get();

        $pendingDiscord = $this->getPendingDiscordUsers($division, request()->boolean('all_pending'));

        return response()->json([
            'name' => $division->name,
            'platoons' => $platoons->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'members_count' => $p->members_count,
                'leader_name' => $p->leader?->name,
                'squads' => $p->squads->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'members_count' => $s->members_count,
                    'leader_name' => $s->leader?->name,
                ]),
            ]),
            'threads' => collect($threads)->map(fn ($t) => [
                'name' => $t['thread_name'] ?? '',
                'url' => $t['thread_url'] ?? '',
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
            'pending_discord' => $pendingDiscord,
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
        if (! is_numeric($member_id) || (int) $member_id < 1) {
            return [
                'is_member' => false,
                'valid_group' => false,
                'group_id' => null,
                'exists_in_tracker' => false,
                'tags' => [],
                'division' => null,
            ];
        }

        $member_id = (int) $member_id;
        $member = Member::where('clan_id', $member_id)->first();
        $existsInTracker = $member !== null;

        $tags = [];
        $division = null;
        if ($member) {
            $tags = $member->tags()
                ->get()
                ->map(fn ($tag) => [
                    'name' => $tag->name,
                    'division' => $tag->division?->abbreviation ?? 'Global',
                ])
                ->toArray();
            $division = $member->division?->abbreviation;
        }

        if (app()->environment() === 'local') {
            return [
                'is_member' => true,
                'valid_group' => true,
                'username' => 'LocalTestUser',
                'exists_in_tracker' => $existsInTracker,
                'tags' => $tags,
                'division' => $division,
            ];
        }

        $result = $this->procedureService->getUser($member_id);

        if (! $result || ! property_exists($result, 'usergroupid')) {
            return [
                'is_member' => false,
                'valid_group' => false,
                'group_id' => null,
                'exists_in_tracker' => $existsInTracker,
                'tags' => $tags,
                'division' => $division,
            ];
        }

        return [
            'is_member' => true,
            'username' => $result->username,
            'valid_group' => ForumGroup::tryFrom((int) $result->usergroupid)?->isEligibleForRecruitment() ?? false,
            'group_id' => (int) $result->usergroupid,
            'exists_in_tracker' => $existsInTracker,
            'tags' => $tags,
            'division' => $division,
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
        $memberId = request('member_id', 0);
        $email = request('email');

        try {
            $result = DB::connection('aod_forums')->select('CALL user_exists(?, ?)', [$name, $memberId]);
            $nameIsTaken = ! empty($result);

            if ($nameIsTaken && $email) {
                $forumService = app(\App\Services\AODForumService::class);
                $existingUser = $forumService->getUserByEmail($email);

                if ($existingUser && strcasecmp($existingUser->username, $name) === 0) {
                    return response()->json([
                        'memberExists' => false,
                        'existingAccount' => true,
                        'existingUserId' => (int) $existingUser->userid,
                    ]);
                }
            }

            return response()->json(['memberExists' => $nameIsTaken]);
        } catch (\Exception $e) {
            return response()->json(['memberExists' => false]);
        }
    }

    public function pendingDiscord(Division $division): JsonResponse
    {
        $this->authorize('recruit', Member::class);

        return response()->json([
            'pending_discord' => $this->getPendingDiscordUsers($division, request()->boolean('all_pending')),
        ]);
    }

    private function getPendingDiscordUsers(Division $division, bool $allPending = false)
    {
        $query = User::pendingDiscord()
            ->whereNotNull('date_of_birth');

        if (! $allPending) {
            $query->where(function ($q) use ($division) {
                $q->whereHas('divisionApplication', fn ($a) => $a->where('division_id', $division->id))
                    ->orWhereDoesntHave('divisionApplication');
            });
        }

        return $query
            ->with('divisionApplication.division')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($u) {
                $application = null;
                if ($u->divisionApplication) {
                    $application = collect($u->divisionApplication->responses)->map(function ($response) {
                        return [
                            'label' => $response['label'] ?? 'Unknown',
                            'value' => is_array($response['value'] ?? null) ? implode(', ', $response['value']) : ($response['value'] ?? '—'),
                        ];
                    })->values();
                }

                return [
                    'id' => $u->id,
                    'discord_username' => $u->discord_username,
                    'forum_name' => $u->name,
                    'discord_id' => $u->discord_id,
                    'email' => $u->email,
                    'created_at' => $u->created_at->diffForHumans(),
                    'application' => $application,
                    'application_division' => $u->divisionApplication?->division?->name,
                ];
            });
    }

    private function createLegacyForumAccount(User $pendingUser, Division $division, AODForumService $forumService): ?object
    {
        $co = $division->members()
            ->where('position', Position::COMMANDING_OFFICER)
            ->first();

        if (! $co) {
            return null;
        }

        $result = AODForumService::createForumAccount(
            impersonatingMemberId: $co->clan_id,
            username: $pendingUser->name,
            email: $pendingUser->email,
            dateOfBirth: $pendingUser->date_of_birth->format('Y-m-d'),
            password: $pendingUser->forum_password,
            discordId: $pendingUser->discord_id,
            forumGroup: ForumGroup::AWAITING_MODERATION,
        );

        if (! $result['success']) {
            return null;
        }

        $pendingUser->update(['forum_password' => null]);

        return $forumService->getUserByEmail($pendingUser->email);
    }

    private function handleNotification($member, $division)
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
