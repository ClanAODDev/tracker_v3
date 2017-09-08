<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNote;
use App\Member;
use App\Note;
use App\Tag;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * @param CreateNote $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateNote $form)
    {
        $form->persist();

        $this->showToast('Note saved successfully');

        return redirect()->back();
    }

    /**
     * @param Member $member
     * @param Note $note
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Member $member, Note $note)
    {
        $this->authorize('delete', $member);

        $division = $member->division;

        if ( ! $division) {
            return redirect(404);
        }

        $tags = ($division)
            ? $division->availableTags->pluck('name', 'id')
            : Tag::all()->whereDefault(true)->get()->pluck('name', 'id');

        return view('member.edit-note', compact(
            'note', 'division', 'member', 'tags'
        ));
    }

    /**
     * @param Request $request
     * @param Member $member
     * @param Note $note
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Member $member, Note $note)
    {
        $this->authorize('delete', $member);
        $note->update(request()->all());
        $this->syncTags($note, $request->input('tag_list'));
        $this->showToast('Note saved successfully');

        return redirect()->route('member', $member->getUrlParams());
    }

    /**
     * Sync up the tags on a note
     *
     * @param Note $note
     * @param array $tags
     */
    private function syncTags(Note $note, array $tags)
    {
        $note->tags()->sync($tags);
    }

    public function delete(Member $member, Note $note)
    {
        $this->authorize('delete', $note);

        // check for existing leave with associated note
        $leave = \App\Leave::whereHas('note')
            ->whereNoteId($note->id)
            ->first();

        if (count($leave)) {
            return redirect()->back()
                ->withErrors(['' => "Note cannot be deleted. A leave of absence associated to this note still exists!"])
                ->withInput();
        }

        $note->delete();
        $this->showToast('Note deleted successfully');

        return redirect()->route('member', $member->getUrlParams());
    }
}
