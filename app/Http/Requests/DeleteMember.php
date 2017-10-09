<?php

namespace App\Http\Requests;

use App\Note;
use App\Tag;
use Illuminate\Foundation\Http\FormRequest;

class DeleteMember extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('delete', [$this->route('member')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tag_list' => 'required|array|min:1',
            'removal_reason' => 'required'
        ];
    }

    /**
     * Persist the form
     */
    public function persist()
    {
        $member = $this->route('member');
        $this->createRemovalNote($member);
        $member->resetPositionsAndAssignments();
    }

    /**
     * @param $member
     */
    private function createRemovalNote($member)
    {
        $note = Note::create([
            'type' => 'negative',
            'body' => $this->input('removal_reason'),
            'author_id' => auth()->id(),
            'member_id' => $member->id
        ]);

        $note->tags()->attach($this->input('tag_list'));
    }

}
