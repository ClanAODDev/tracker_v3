<?php

namespace App\Http\Controllers;

use App\Http\Requests\Note\CreateNote;
use App\Models\Leave;
use App\Models\Member;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(CreateNote $form): RedirectResponse
    {
        $form->persist();

        $this->showSuccessToast('Note saved successfully');

        return redirect()->back();
    }

    public function update(Request $request, Member $member, Note $note): RedirectResponse
    {
        $this->authorize('edit', $note);

        $request->validate(
            ['body' => 'required', 'forum_thread_id' => 'nullable|numeric'],
            [
                'body.required'           => 'You must provide content for your note',
                'forum_thread_id.numeric' => 'Forum thread ID must be a number',
            ]
        );

        $note->update($request->only(['body', 'type', 'forum_thread_id']));

        $this->showSuccessToast('Note saved successfully');

        return redirect()->route('member', $member->getUrlParams());
    }

    public function delete(Member $member, Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $leaveExists = Leave::whereHas('note')
            ->whereNoteId($note->id)
            ->exists();

        if ($leaveExists) {
            $this->showErrorToast('Note cannot be deleted. A leave of absence associated to this note still exists!');

            return redirect(route('member', $member->getUrlParams()));
        }

        $note->delete();
        $this->showSuccessToast('Note deleted successfully');

        return redirect()->route('member', $member->getUrlParams());
    }

    public function restore(Member $member, int $noteId): RedirectResponse
    {
        $note = Note::onlyTrashed()->where('id', $noteId)->where('member_id', $member->id)->firstOrFail();

        $this->authorize('restore', $note);

        $note->restore();
        $this->showSuccessToast('Note restored');

        return redirect()->back();
    }

    public function forceDelete(Member $member, int $noteId): RedirectResponse
    {
        $note = Note::onlyTrashed()->where('id', $noteId)->where('member_id', $member->id)->firstOrFail();

        $this->authorize('forceDelete', $note);

        $note->forceDelete();
        $this->showSuccessToast('Note permanently deleted');

        return redirect()->back();
    }
}
