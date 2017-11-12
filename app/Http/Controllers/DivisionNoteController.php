<?php

namespace App\Http\Controllers;

use App\Division;
use App\Tag;
use Illuminate\Http\Request;

class DivisionNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Division $division
     * @param Tag|null $tag
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Division $division, Tag $tag = null)
    {
        $tags = $division->notes->pluck('tags')->flatten()->unique('slug');

        $notes = ($tag->exists)
            ? $division->notes->filter(function ($note) use ($tag) {
                return $note->tags->contains($tag->id);
            })
            : $division->notes;

        $notes = $notes->sortByDesc('created_at');

        return view('division.notes', compact('division', 'tags', 'notes'))->with(['filter' => $tag]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public
    function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public
    function store(
        Request $request
    ) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public
    function show(
        $id
    ) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public
    function edit(
        $id
    ) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public
    function update(
        Request $request,
        $id
    ) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public
    function destroy(
        $id
    ) {
        //
    }
}
