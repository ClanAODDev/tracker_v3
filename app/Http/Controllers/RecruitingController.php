<?php

namespace App\Http\Controllers;

use App\Enums\ForumGroup;
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
use Illuminate\View\View;

class RecruitingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ForumProcedureService $procedureService,
        protected RecruitmentService $recruitmentService,
        protected AODForumService $forumService,
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

        $division    = Division::whereSlug($request->division)->first();
        $recruiterId = auth()->user()->member->clan_id;

        if ($request->pending_user_id) {
            return $this->recruitPendingDiscordUser($request, $division, $recruiterId);
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

        $this->finalizeRecruitment($member, $division, $recruiterId);

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
        $threads  = $settings->get('recruiting_threads', []);
        $tasks    = $settings->get('recruiting_tasks', []);

        $platoons = $division->platoons()
            ->withCount('members')
            ->with([
                'leader:clan_id,name',
                'squads' => fn ($q) => $q->withCount('members')->with('leader:clan_id,name'),
            ])
            ->get();

        $pendingDiscord = $this->getPendingDiscordUsers($division, request()->boolean('all_pending'));

        return response()->json([
            'name'     => $division->name,
            'platoons' => $platoons->map(fn ($p) => [
                'id'            => $p->id,
                'name'          => $p->name,
                'members_count' => $p->members_count,
                'leader_name'   => $p->leader?->name,
                'squads'        => $p->squads->map(fn ($s) => [
                    'id'            => $s->id,
                    'name'          => $s->name,
                    'members_count' => $s->members_count,
                    'leader_name'   => $s->leader?->name,
                ]),
            ]),
            'threads' => collect($threads)->map(fn ($t) => [
                'name'     => $t['thread_name'] ?? '',
                'url'      => $t['thread_url'] ?? '',
                'comments' => $t['comments'] ?? '',
                'read'     => false,
            ]),
            'tasks' => collect($tasks)->map(fn ($t) => [
                'description' => $t['task_description'] ?? '',
                'complete'    => false,
            ]),
            'welcome_area'       => $settings->get('welcome_area', ''),
            'welcome_pm'         => $settings->get('welcome_pm', ''),
            'use_welcome_thread' => $settings->get('use_welcome_thread', false),
            'locality'           => [
                'platoon' => $division->locality('platoon'),
                'squad'   => $division->locality('squad'),
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
                'is_member'         => false,
                'valid_group'       => false,
                'group_id'          => null,
                'exists_in_tracker' => false,
                'tags'              => [],
                'division'          => null,
            ];
        }

        $member_id       = (int) $member_id;
        $member          = Member::where('clan_id', $member_id)->first();
        $existsInTracker = $member !== null;

        $tags     = [];
        $division = null;
        if ($member) {
            $tags = $member->tags()
                ->get()
                ->map(fn ($tag) => [
                    'name'     => $tag->name,
                    'division' => $tag->division?->abbreviation ?? 'Global',
                ])
                ->toArray();
            $division = $member->division?->abbreviation;
        }

        if (app()->environment() === 'local') {
            return [
                'is_member'         => true,
                'valid_group'       => true,
                'username'          => 'LocalTestUser',
                'exists_in_tracker' => $existsInTracker,
                'tags'              => $tags,
                'division'          => $division,
            ];
        }

        $result = $this->procedureService->getUser($member_id);

        if (! $result || ! property_exists($result, 'usergroupid')) {
            return [
                'is_member'         => false,
                'valid_group'       => false,
                'group_id'          => null,
                'exists_in_tracker' => $existsInTracker,
                'tags'              => $tags,
                'division'          => $division,
            ];
        }

        return [
            'is_member'         => true,
            'username'          => $result->username,
            'valid_group'       => ForumGroup::tryFrom((int) $result->usergroupid)?->isEligibleForRecruitment() ?? false,
            'group_id'          => (int) $result->usergroupid,
            'exists_in_tracker' => $existsInTracker,
            'tags'              => $tags,
            'division'          => $division,
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

        $name     = request('name');
        $memberId = request('member_id', 0);
        $email    = request('email');

        $forumService = app(AODForumService::class);
        $nameIsTaken  = $forumService->userExists($name, $memberId);

        if ($nameIsTaken && $email) {
            $existingUser = $forumService->getUserByEmail($email);

            if ($existingUser && strcasecmp($existingUser->username, $name) === 0) {
                return response()->json([
                    'memberExists'    => false,
                    'existingAccount' => true,
                    'existingUserId'  => (int) $existingUser->userid,
                ]);
            }
        }

        return response()->json(['memberExists' => $nameIsTaken]);
    }

    public function checkForumEmail(Request $request): JsonResponse
    {
        $this->authorize('recruit', Member::class);

        $request->validate(['email' => 'required|email']);

        if (app()->environment() === 'local') {
            return response()->json([
                'found' => false,
            ]);
        }

        $forumService = app(AODForumService::class);
        $forumUser    = $forumService->getUserByEmail($request->email);

        if (! $forumUser) {
            return response()->json([
                'found' => false,
            ]);
        }

        $userId       = (int) $forumUser->userid;
        $forumProfile = $this->procedureService->getUser($userId);

        if (! $forumProfile || ! property_exists($forumProfile, 'usergroupid')) {
            return response()->json([
                'found' => false,
            ]);
        }

        $groupId  = (int) $forumProfile->usergroupid;
        $group    = ForumGroup::tryFrom($groupId);
        $eligible = $group?->isEligibleForRecruitment() ?? false;

        return response()->json([
            'found'            => true,
            'user_id'          => $userId,
            'username'         => $forumProfile->username ?? $forumUser->username,
            'group_id'         => $groupId,
            'eligible'         => $eligible,
            'rejection_reason' => $eligible ? null : $group?->recruitmentRejectionReason(),
        ]);
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
                    'id'                   => $u->id,
                    'discord_username'     => $u->discord_username,
                    'forum_name'           => $u->name,
                    'discord_id'           => $u->discord_id,
                    'email'                => $u->email,
                    'created_at'           => $u->created_at->diffForHumans(),
                    'application'          => $application,
                    'application_division' => $u->divisionApplication?->division?->name,
                ];
            });
    }

    private function createForumAccountForPendingUser(
        User $pendingUser,
        string $forumName,
        int $recruiterId,
    ): ?object {
        $result = AODForumService::createForumAccount(
            impersonatingMemberId: $recruiterId,
            username: $forumName,
            email: $pendingUser->email,
            dateOfBirth: $pendingUser->date_of_birth->format('Y-m-d'),
            password: $pendingUser->forum_password,
            discordId: $pendingUser->discord_id,
            forumGroup: ForumGroup::AWAITING_MODERATION,
        );

        if (! $result['success']) {
            \Log::channel('recruiting')->warning('Forum account creation failed', [
                'error'   => $result['error'] ?? 'Unknown error',
                'payload' => [
                    'aod_userid'  => $recruiterId,
                    'username'    => $forumName,
                    'email'       => $pendingUser->email,
                    'dob'         => $pendingUser->date_of_birth->format('Y-m-d'),
                    'discord_id'  => $pendingUser->discord_id,
                    'usergroupid' => ForumGroup::AWAITING_MODERATION->value,
                ],
            ]);

            return null;
        }

        $pendingUser->update(['forum_password' => null]);

        return $this->forumService->getUserByEmail($pendingUser->email);
    }

    private function recruitPendingDiscordUser(Request $request, Division $division, int $recruiterId)
    {
        $pendingUser = User::pendingDiscord()->find($request->pending_user_id);

        if (! $pendingUser) {
            return response()->json([
                'message' => 'Pending Discord user not found.',
            ], 422);
        }

        \Log::channel('recruiting')->info('Discord recruitment started', [
            'pending_user_id'  => $pendingUser->id,
            'discord_id'       => $pendingUser->discord_id,
            'discord_username' => $pendingUser->discord_username,
            'email'            => $pendingUser->email,
            'has_password'     => ! empty($pendingUser->forum_password),
            'recruiter_id'     => $recruiterId,
        ]);

        $forumUser = $this->forumService->getUserByEmail($pendingUser->email);

        \Log::channel('recruiting')->info('Forum account lookup by email', [
            'found'   => $forumUser !== null,
            'user_id' => $forumUser?->userid,
        ]);

        if (! $forumUser && $pendingUser->forum_password) {
            \Log::channel('recruiting')->info('No existing forum account — creating new account', [
                'forum_name' => $request->forum_name,
            ]);

            $forumUser = $this->createForumAccountForPendingUser($pendingUser, $request->forum_name, $recruiterId);

            \Log::channel('recruiting')->info('Forum account creation result', [
                'success' => $forumUser !== null,
                'user_id' => $forumUser?->userid,
            ]);

            if (! $forumUser) {
                return response()->json([
                    'message' => 'Failed to create forum account. Please try again or contact an administrator.',
                ], 422);
            }
        }

        if (! $forumUser) {
            \Log::channel('recruiting')->warning('Discord recruitment aborted — no forum account and no password', [
                'pending_user_id' => $pendingUser->id,
            ]);

            return response()->json([
                'message' => 'No forum account found for this user and no password is available to create one. '
                    . 'The user may need to re-register through Discord.',
            ], 422);
        }

        $clanId       = (int) $forumUser->userid;
        $forumProfile = $this->procedureService->getUser($clanId);

        \Log::channel('recruiting')->info('Forum profile fetched', [
            'clan_id'        => $clanId,
            'found'          => $forumProfile !== null,
            'usergroupid'    => $forumProfile?->usergroupid,
            'membergroupids' => $forumProfile->membergroupids ?? null,
        ]);

        if ($forumProfile && property_exists($forumProfile, 'usergroupid')) {
            $group = ForumGroup::tryFrom((int) $forumProfile->usergroupid);

            if ($group && ! $group->isEligibleForRecruitment()) {
                \Log::channel('recruiting')->warning('Discord recruitment blocked — ineligible forum group', [
                    'clan_id' => $clanId,
                    'group'   => $group->name,
                ]);

                return response()->json([
                    'message' => $group->recruitmentRejectionReason(),
                ], 422);
            }
        }

        $discordResult = $this->procedureService->setDiscordInfo(
            userId: $clanId,
            discordId: $pendingUser->discord_id,
            discordTag: $pendingUser->discord_username ?? '',
        );

        \Log::channel('recruiting')->info('Set discord info on forum profile', [
            'clan_id'          => $clanId,
            'discord_id'       => $pendingUser->discord_id,
            'discord_username' => $pendingUser->discord_username,
            'rows_matched'     => $discordResult?->rows_matched,
            'rows_affected'    => $discordResult?->rows_affected,
        ]);

        if (! $discordResult || ! $discordResult->rows_matched) {
            \Log::channel('recruiting')->error('Discord recruitment aborted — forum account not found', [
                'clan_id' => $clanId,
            ]);

            return response()->json([
                'message' => 'Forum account not found for this user. Please contact an administrator.',
            ], 422);
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

        \Log::channel('recruiting')->info('Member created via Discord recruitment', [
            'member_id' => $member->id,
            'clan_id'   => $clanId,
        ]);

        $pendingUser->update(['member_id' => $member->id]);

        DivisionApplication::where('user_id', $pendingUser->id)->get()->each->delete();

        $this->finalizeRecruitment($member, $division, $recruiterId);

        $this->showSuccessToast('Recruitment completed for Discord user.');
    }

    private function finalizeRecruitment(Member $member, Division $division, int $recruiterId): void
    {
        $this->recruitmentService->createMemberRequest($member, $division, $recruiterId);

        $this->handleNotification($member, $division);

        SyncDiscordMember::dispatch($member);
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
