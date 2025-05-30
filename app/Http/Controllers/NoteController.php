<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNote;
use App\Models\Leave;
use App\Models\Member;
use App\Models\Note;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    /**
     * @return RedirectResponse
     */
    public function store(CreateNote $form)
    {
        $form->persist();

        $this->showSuccessToast('Note saved successfully');

        return redirect()->back();
    }

    /**
     * @return Factory|View
     *
     * @throws AuthorizationException
     */
    public function edit(Member $member, Note $note)
    {
        $this->authorize('separate', $member);

        $division = $member->division;

        if (! $division) {
            return redirect(404);
        }

        return view('member.edit-note', compact(
            'note',
            'division',
            'member'
        ));
    }

    /**
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function update(Request $request, Member $member, Note $note)
    {
        $this->authorize('separate', $member);

        $this->validate(request(), [
            'body.required' => 'You must provide content for your note',
            'forum_thread_id.numeric' => 'Forum thread ID must be a number',
        ]);

        $note->update(request()->all());

        $this->showSuccessToast('Note saved successfully');

        return redirect()->route('member', $member->getUrlParams());
    }

    public function delete(Member $member, Note $note)
    {
        $this->authorize('delete', $note);

        // check for existing leave with associated note
        $leave = Leave::whereHas('note')
            ->whereNoteId($note->id)
            ->exists();

        if ($leave) {
            $this->showErrorToast('Note cannot be deleted. A leave of absence associated to this note still exists!');

            return redirect(route('member', $member->getUrlParams()));
        }

        $note->delete();
        $this->showSuccessToast('Note deleted successfully');

        return redirect()->route('member', $member->getUrlParams());
    }
}
