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
            ? $division->notes()->whereType($type)->get()
            : $division->notes()->get();

        $notes = $notes->load('member.rank')->sortByDesc('created_at');

        return view('division.notes', compact('division', 'notes'))
            ->with(['filter' => $type]);
    }
}
