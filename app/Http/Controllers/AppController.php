<?php

namespace App\Http\Controllers;

use App\Data\DivisionLeaderboardData;
use App\Data\PendingAction;
use App\Data\PendingActionsData;
use App\Models\Division;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Support\DivisionToolbar;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class AppController extends Controller
{
    public function changeLog(): Response
    {
        $markdown = file_get_contents(resource_path('views/changelog.md'));

        return Inertia::render('changelog', [
            'body' => (string) Markdown::convertToHtml($markdown),
        ]);
    }

    public function index(): Response
    {
        $user       = auth()->user();
        $myDivision = $user->member->division;

        $pendingActions = PendingActionsData::forDivision($myDivision, $user);
        $leaderboard    = DivisionLeaderboardData::forUser($user);

        $divisions = Division::active()
            ->withoutFloaters()
            ->withCount('members')
            ->orderBy('name')
            ->get()
            ->except($myDivision->id);

        $statsByDivision = [];
        foreach ($leaderboard->voiceLeaders as $entry) {
            $statsByDivision[$entry['id']]['voice'] = $entry['formatted'];
        }
        foreach ($leaderboard->recruitLeaders as $entry) {
            $statsByDivision[$entry['id']]['recruits'] = $entry['value'];
        }

        return Inertia::render('home', [
            'myDivision' => [
                'name'                => $myDivision->name,
                'slug'                => $myDivision->slug,
                'abbr'                => $myDivision->abbreviation,
                'logo'                => $myDivision->getLogoPath(),
                'memberCount'         => $myDivision->members()->count(),
                'isShutdown'          => $myDivision->isShutdown(),
                'canManage'           => $user->can('update', $myDivision),
                'manageUrl'           => route('filament.mod.resources.divisions.edit', $myDivision),
                'requestsUrl'         => route('filament.mod.resources.member-requests.index'),
                'canManageRequests'   => $user->can('manage', MemberRequest::class),
                'canRecruit'          => $user->can('recruit', Member::class),
                'applicationRequired' => (bool) $myDivision->settings()->get('application_required', false),
            ],
            'toolbar'        => DivisionToolbar::for($myDivision, $user, includeHome: true),
            'pendingActions' => $pendingActions->actions->map(fn (PendingAction $action) => [
                'key'   => $action->key,
                'count' => $action->count,
                'url'   => $action->url,
                'icon'  => $action->icon,
                'label' => $action->label,
                'style' => $action->style,
            ]),
            'leaderboard' => [
                'userDivisionId' => $leaderboard->userDivisionId,
                'recruits'       => $leaderboard->recruitLeaders->take(10)->values(),
                'voice'          => $leaderboard->voiceLeaders->take(10)->values(),
                'growth'         => $leaderboard->growthLeaders->take(10)->values(),
            ],
            'divisions' => $divisions->map(fn (Division $division) => [
                'name'        => $division->name,
                'slug'        => $division->slug,
                'logo'        => $division->getLogoPath(),
                'memberCount' => $division->members_count,
                'isShutdown'  => $division->isShutdown(),
                'voice'       => $statsByDivision[$division->id]['voice'] ?? null,
                'recruits'    => $statsByDivision[$division->id]['recruits'] ?? 0,
            ])->values(),
        ]);
    }
}
