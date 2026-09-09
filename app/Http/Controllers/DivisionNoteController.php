<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\DivisionTag;
use App\Models\Note;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class DivisionNoteController extends Controller
{
    #[Authorize('show', Note::class)]
    public function index(Division $division): Response
    {

        $type   = request('type');
        $search = request('search');
        $tagId  = request('tag');

        $notes = $type
            ? $division->notes()->whereType($type)
            : $division->notes();

        $notes = $notes
            ->where('member_id', '!=', auth()->user()->member_id)
            ->with(['member:id,name,clan_id,rank', 'author:id,name']);

        if ($search) {
            $notes = $notes->where(function ($query) use ($search) {
                $query->where('body', 'like', "%{$search}%")
                    ->orWhereHas('member', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($tagId) {
            $notes = $notes->whereHas('member.tags', fn ($q) => $q->where('division_tags.id', $tagId));
        }

        $notes = $notes
            ->orderByDesc('created_at')
            ->get()
            ->filter(function ($note) {
                if ($note->type === 'sr_ldr') {
                    return auth()->user()->isRole(['sr_ldr', 'admin']);
                }

                return true;
            });

        $tags = DivisionTag::forDivision($division->id)
            ->visibleTo()
            ->orderBy('name')
            ->get();

        return Inertia::render('division/notes', [
            'division'  => ['name' => $division->name, 'slug' => $division->slug],
            'noteTypes' => Note::allNoteTypes(),
            'tags'      => $tags->map(fn ($tag) => [
                'id'   => $tag->id,
                'name' => $tag->name . ($tag->isGlobal() ? ' (Clan-wide)' : ''),
            ])->values(),
            'filters' => [
                'type'   => $type,
                'search' => $search,
                'tag'    => $tagId ? (int) $tagId : null,
            ],
            'notes' => $notes->map(fn (Note $note) => [
                'id'         => $note->id,
                'type'       => $note->type,
                'body'       => $note->body,
                'memberName' => $note->member->present()->rankName(),
                'memberUrl'  => route('member', $note->member->getUrlParams()) . '?notes=1',
                'author'     => $note->author?->name ?? 'Unknown',
                'date'       => $note->updated_at->format('M j, Y'),
            ])->values(),
        ]);
    }
}
