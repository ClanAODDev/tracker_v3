<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNote;
use App\Member;
use App\Note;
use App\Tag;
use Illuminate\Http\Request;
use Toastr;

class NoteController extends Controller
{
    /**
     * @param CreateNote $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateNote $form)
    {
        $form->persist();

        Toastr::success('Note saved successfully!', "Create Member Note", [
            'positionClass' => 'toast-top-right',
            'progressBar' => true
        ]);

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

        $division = $member->primaryDivision;

        $tags = Tag::pluck('name', 'id');

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

        Toastr::success('Note saved successfully!', "Update Member Note", [
            'positionClass' => 'toast-top-right',
            'progressBar' => true
        ]);

        return redirect()->route('member', $member->clan_id);
    }

    public function delete()
    {

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
}
