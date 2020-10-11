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
     * @param Division $division
     * @return Factory|View
     */
    public function index(Division $division)
    {
        $this->authorize('show', Note::class);

        $notes = ($type = request('type'))
            ? $division->notes()->whereType($type)
            : $division->notes();

        // omit own notes for security
        $notes = $notes
            ->where('member_id', '!=', auth()->user()->member_id)
            ->with('member.rank')->orderByDesc('created_at')
            ->get()
            ->filter(function ($note) {
                if ($note->type == 'sr_ldr') {
                    return auth()->user()->isRole(['sr_ldr', 'admin']);
                }

                return true;
            });

        return view('division.notes', compact('division', 'notes'))
            ->with(['filter' => $type]);
    }
}
