<?php

namespace App\Http\Controllers;

use App\Http\Requests\Note\CreateNote;
use App\Models\Leave;
use App\Models\Member;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function store(CreateNote $form): RedirectResponse
    {
        $form->persist();

        $this->showSuccessToast('Note saved successfully');

        return redirect()->back();
    }

    public function edit(Member $member, Note $note): View
    {
        $this->authorize('separate', $member);

        $division = $member->division;

        if (! $division) {
            abort(404);
        }

        return view('member.edit-note', compact('note', 'division', 'member'));
    }

    public function update(Request $request, Member $member, Note $note): RedirectResponse
    {
        $this->authorize('separate', $member);

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

    public function restore(Member $member, int $noteId): JsonResponse
    {
        $note = Note::onlyTrashed()->where('id', $noteId)->where('member_id', $member->id)->first();

        if (! $note) {
            return response()->json(['success' => false, 'message' => 'Note not found'], 404);
        }

        $this->authorize('restore', $note);

        $note->restore();

        return response()->json(['success' => true]);
    }

    public function forceDelete(Member $member, int $noteId): JsonResponse
    {
        $note = Note::onlyTrashed()->where('id', $noteId)->where('member_id', $member->id)->first();

        if (! $note) {
            return response()->json(['success' => false, 'message' => 'Note not found'], 404);
        }

        $this->authorize('forceDelete', $note);

        $note->forceDelete();

        return response()->json(['success' => true]);
    }
}
