<?php

namespace App\Http\Controllers;

use App\Enums\ForumGroup;
use App\Exceptions\RecruitmentFailedException;
use App\Http\Requests\Recruiting\CheckForumEmailRequest;
use App\Http\Requests\Recruiting\SubmitRecruitmentRequest;
use App\Http\Requests\Recruiting\ValidateMemberNameRequest;
use App\Jobs\SyncDiscordMember;
use App\Models\Division;
use App\Models\Member;
use App\Models\User;
use App\Notifications\Channel\NotifyDivisionNewExternalRecruit;
use App\Notifications\Channel\NotifyDivisionNewMemberRecruited;
use App\Services\AODForumService;
use App\Services\DiscordRecruitmentService;
use App\Services\ForumProcedureService;
use App\Services\RecruitmentService;
use App\Transformers\MemberDiscordMatchTransformer;
use App\Transformers\PendingDiscordUserTransformer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

#[Middleware('auth')]
class RecruitingController extends Controller
{
    public function __construct(
        protected ForumProcedureService $procedureService,
        protected RecruitmentService $recruitmentService,
        protected AODForumService $forumService,
        protected DiscordRecruitmentService $discordRecruitmentService,
    ) {}

    /**
     * @return mixed
     */
    #[Authorize('recruit', Member::class)]
    public function index()
    {
        $divisions = Division::recruitable()->get();

        return view('recruit.index', compact('divisions'));
    }

    /**
     * @throws AuthorizationException
     */
    #[Authorize('recruit', Member::class)]
    public function submitRecruitment(SubmitRecruitmentRequest $request)
    {

        $division  = Division::whereSlug($request->division)->first();
        $recruiter = auth()->user()->member;

        if ($request->pending_user_id) {
            return $this->recruitPendingDiscordUser($request, $division, $recruiter);
        }

        return $this->withRecruitLock(
            'recruit:member:' . (int) $request->member_id,
            'This member is already being recruited. Please wait a moment and try again.',
            function () use ($request, $division, $recruiter) {
                $member = $this->recruitmentService->createMember(
                    (int) $request->member_id,
                    $request->forum_name,
                    $division,
                    (int) $request->rank,
                    (int) $request->platoon,
                    $request->squad ? (int) $request->squad : null,
                    $request->ingame_name,
                    $recruiter
                );

                $this->finalizeRecruitment($member, $division, $recruiter);

                $this->showSuccessToast('Your recruitment has successfully been completed!');
            }
        );
    }

    private function withRecruitLock(string $key, string $busyMessage, callable $callback)
    {
        $lock = Cache::lock($key, 30);

        if (! $lock->get()) {
            return response()->json(['message' => $busyMessage], 409);
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }

    #[Authorize('recruit', Member::class)]
    public function form(Division $division)
    {
        if ($division->isShutdown()) {
            $this->showErrorToast('This division has been shutdown and cannot receive new members');

            return redirect()->back();
        }

        return view('recruit.form', compact('division'));
    }

    #[Authorize('recruit', Member::class)]
    public function discordConfirm(string $discordId)
    {
        $pendingUser = User::pendingDiscord()
            ->whereNotNull('date_of_birth')
            ->where('discord_id', $discordId)
            ->with('divisionApplication.division')
            ->first();

        // The applicant's own division application, if any, is the only
        // reliable source for where they should be recruited to — the
        // caller constructing this link has no way of knowing that.
        $targetDivision = $pendingUser?->divisionApplication?->division;

        if ($targetDivision?->isShutdown()) {
            $this->showErrorToast('This division has been shutdown and cannot receive new members');

            return redirect()->route('recruiting.initial');
        }

        return view('recruit.discord-confirm', [
            'targetDivision' => $targetDivision,
            'discordId'      => $discordId,
            'forumAccount'   => $pendingUser ? $this->checkForumAccountForEmail($pendingUser->email) : null,
            'pendingUser'    => $pendingUser ? (new PendingDiscordUserTransformer)->transform($pendingUser) : null,
            'memberMatches'  => $pendingUser ? null : $this->findMembersByDiscordId($discordId),
            'divisions'      => $targetDivision ? null : Division::recruitable()->get(),
        ]);
    }

    private function findMembersByDiscordId(string $discordId): Collection
    {
        $matches = Member::where('discord_id', $discordId)
            ->with('division')
            ->get();

        return collect((new MemberDiscordMatchTransformer)->transformCollection($matches->all()));
    }

    #[Authorize('recruit', Member::class)]
    public function getDivisionRecruitData(Division $division): JsonResponse
    {

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
                'discord_matches'   => [],
                'discord_id'        => '123456789012345678',
                'discord_username'  => 'localtestuser',
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
                'discord_matches'   => [],
            ];
        }

        $discordMatches = $this->findDiscordMatches($member_id, $result, $member);

        return [
            'is_member'         => true,
            'username'          => $result->username,
            'valid_group'       => ForumGroup::tryFrom((int) $result->usergroupid)?->isEligibleForRecruitment() ?? false,
            'group_id'          => (int) $result->usergroupid,
            'exists_in_tracker' => $existsInTracker,
            'tags'              => $tags,
            'division'          => $division,
            'discord_matches'   => $discordMatches,
            'discord_id'        => $result->discord_id ?? null,
            'discord_username'  => $result->discord_tag ?? null,
        ];
    }

    public function validateMemberName(ValidateMemberNameRequest $request): JsonResponse
    {
        if (app()->environment() === 'local') {
            return response()->json(['memberExists' => false]);
        }

        $name     = $request->string('name')->toString();
        $memberId = $request->integer('member_id');
        $email    = $request->string('email')->toString();

        $nameIsTaken = $this->forumService->userExists($name, $memberId);

        if ($nameIsTaken && $email) {
            $existingUser = $this->forumService->getUserByEmail($email);

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

    #[Authorize('recruit', Member::class)]
    public function checkForumEmail(CheckForumEmailRequest $request): JsonResponse
    {
        return response()->json($this->checkForumAccountForEmail($request->email));
    }

    private function checkForumAccountForEmail(string $email): array
    {
        if (app()->environment() === 'local') {
            return ['found' => false];
        }

        $forumUser = $this->forumService->getUserByEmail($email);

        if (! $forumUser) {
            return ['found' => false];
        }

        $userId       = (int) $forumUser->userid;
        $forumProfile = $this->procedureService->getUser($userId);

        if (! $forumProfile || ! property_exists($forumProfile, 'usergroupid')) {
            return ['found' => false];
        }

        $groupId  = (int) $forumProfile->usergroupid;
        $group    = ForumGroup::tryFrom($groupId);
        $eligible = $group?->isEligibleForRecruitment() ?? false;

        return [
            'found'            => true,
            'user_id'          => $userId,
            'username'         => $forumProfile->username ?? $forumUser->username,
            'group_id'         => $groupId,
            'eligible'         => $eligible,
            'rejection_reason' => $eligible ? null : $group?->recruitmentRejectionReason(),
        ];
    }

    #[Authorize('recruit', Member::class)]
    public function pendingDiscord(Division $division): JsonResponse
    {

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

        $pendingUsers = $query
            ->with('divisionApplication.division')
            ->orderBy('created_at', 'desc')
            ->get();

        return collect((new PendingDiscordUserTransformer)->transformCollection($pendingUsers->all()));
    }

    private function recruitPendingDiscordUser(Request $request, Division $division, Member $recruiter)
    {
        $pendingUser = User::pendingDiscord()->find($request->pending_user_id);

        if (! $pendingUser) {
            return response()->json([
                'message' => 'Pending Discord user not found.',
            ], 422);
        }

        return $this->withRecruitLock(
            'recruit:pending-discord:' . $pendingUser->id,
            'This user is already being recruited. Please wait a moment and try again.',
            function () use ($request, $division, $recruiter, $pendingUser) {
                try {
                    $member = $this->discordRecruitmentService->recruit($pendingUser, $request, $division, $recruiter);
                } catch (RecruitmentFailedException $e) {
                    return response()->json(['message' => $e->getMessage()], 422);
                }

                $this->finalizeRecruitment($member, $division, $recruiter);

                $this->showSuccessToast('Recruitment completed for Discord user.');
            }
        );
    }

    private function finalizeRecruitment(Member $member, Division $division, Member $recruiter): void
    {
        $this->recruitmentService->createMemberRequest($member, $division, $recruiter);

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

    private function findDiscordMatches(int $memberId, object $result, ?Member $existingMember): array
    {
        $discordId = property_exists($result, 'discord_id') ? $result->discord_id : null;

        if (! $discordId && $existingMember?->discord_id) {
            $discordId = $existingMember->discord_id;
        }

        if (! $discordId) {
            return [];
        }

        $matches = Member::where('discord_id', $discordId)
            ->where('clan_id', '!=', $memberId)
            ->with('division:id,name')
            ->get();

        return (new MemberDiscordMatchTransformer)->transformCollection($matches->all());
    }
}
