<?php

namespace App\Http\Controllers;

use App\Division;
use App\Note;

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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
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
            ->get();

        return view('division.notes', compact('division', 'notes'))
            ->with(['filter' => $type]);
    }
}
