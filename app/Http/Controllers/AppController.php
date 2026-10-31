<?php

namespace App\Http\Controllers;

use App\Data\DivisionLeaderboardData;
use App\Data\PendingAction;
use App\Data\PendingActionsData;
use App\Enums\Ability;
use App\Models\Division;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\ReleaseNote;
use App\Models\User;
use App\Support\DivisionToolbar;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class AppController extends Controller
{
    public function changeLog(): Response
    {
        $notes = ReleaseNote::published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('changelog/index', [
            'notes' => $notes->getCollection()->map(fn (ReleaseNote $note) => [
                'id'          => $note->id,
                'title'       => $note->title,
                'version'     => $note->version,
                'publishedAt' => $note->published_at->format('M j, Y'),
                'excerpt'     => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) Markdown::convertToHtml($note->body)))), 240),
                'url'         => route('changelog.show', $note->slug),
            ])->values(),
            'pagination' => [
                'total'   => $notes->total(),
                'prevUrl' => $notes->previousPageUrl(),
                'nextUrl' => $notes->nextPageUrl(),
            ],
            'manageUrl' => $this->canManageReleaseNotes()
                ? route('filament.admin.resources.release-notes.index')
                : null,
        ]);
    }

    public function showChangelog(ReleaseNote $releaseNote): Response
    {
        abort_unless($releaseNote->status() === 'published', 404);

        $link = fn (?ReleaseNote $note) => $note ? ['title' => $note->title, 'url' => route('changelog.show', $note->slug)] : null;

        return Inertia::render('changelog/show', [
            'note' => [
                'title'       => $releaseNote->title,
                'version'     => $releaseNote->version,
                'publishedAt' => $releaseNote->published_at->format('M j, Y'),
                'html'        => (string) Markdown::convertToHtml($releaseNote->body),
            ],
            'newer'    => $link($releaseNote->newer()),
            'older'    => $link($releaseNote->older()),
            'indexUrl' => route('changelog'),
            'editUrl'  => $this->canManageReleaseNotes()
                ? route('filament.admin.resources.release-notes.edit', $releaseNote)
                : null,
        ]);
    }

    private function canManageReleaseNotes(): bool
    {
        $user = auth()->user();

        return $user->isDeveloper() || $user->can(Ability::AccessAdminPanel);
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
                'name'                 => $myDivision->name,
                'slug'                 => $myDivision->slug,
                'abbr'                 => $myDivision->abbreviation,
                'logo'                 => $myDivision->getLogoPath(),
                'memberCount'          => $myDivision->members()->count(),
                'isShutdown'           => $myDivision->isShutdown(),
                'canManage'            => $user->can('update', $myDivision),
                'manageUrl'            => route('filament.mod.resources.divisions.edit', $myDivision),
                'requestsUrl'          => route('filament.mod.resources.member-requests.index'),
                'canManageRequests'    => $user->can('manage', MemberRequest::class),
                'canRecruit'           => $user->can('recruit', Member::class),
                'applicationRequired'  => (bool) $myDivision->settings()->get('application_required', false),
                'canManageUnassigned'  => $user->can('manageUnassigned', User::class),
                'unassignedToSquadUrl' => route('division.unassigned-to-squad', $myDivision),
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
