<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Note;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class DivisionNoteController extends Controller
{
    /**
     * DivisionNoteController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index(Division $division)
    {
        $this->authorize('show', Note::class);

        $type = request('type');
        $search = request('search');

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

        return view('division.notes', compact('division', 'notes', 'type', 'search', 'noteTypes'));
    }
}
