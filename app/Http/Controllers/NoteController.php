<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNote;
use App\Member;
use App\Note;
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Member $member, Note $note)
    {
        $this->authorize('delete', $member);

        $division = $member->division;

        if ( ! $division) {
            return redirect(404);
        }

        return view('member.edit-note', compact(
            'note',
            'division',
            'member'
        ));
    }

    /**
     * @param Request $request
     * @param Member $member
     * @param Note $note
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Member $member, Note $note)
    {
        $this->authorize('delete', $member);

        $this->validate(request(), [
            'body.required' => 'You must provide content for your note',
            'forum_thread_id.numeric' => 'Forum thread ID must be a number'
        ]);

        $note->update(request()->all());

        $this->showToast('Note saved successfully');

        return redirect()->route('member', $member->getUrlParams());
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
