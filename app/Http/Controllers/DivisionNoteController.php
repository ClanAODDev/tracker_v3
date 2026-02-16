<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\DivisionTag;
use App\Models\Note;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class DivisionNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return Factory|View
     */
    public function index(Division $division)
    {
        $this->authorize('show', Note::class);

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

        $noteTypes = Note::allNoteTypes();

        $tags = DivisionTag::forDivision($division->id)
            ->visibleTo()
            ->orderBy('name')
            ->get();

        return view('division.notes', compact('division', 'notes', 'type', 'search', 'noteTypes', 'tags', 'tagId'));
    }
}
