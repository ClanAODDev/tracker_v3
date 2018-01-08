<?php

namespace App\Http\Requests;

use App\Note;
use App\Notifications\MemberRemoved;
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

        if ($member->division->settings()->get('slack_alert_removed_member')) {
            $member->division->notify(new MemberRemoved($member, $this->removal_reason));
        }

        $this->createRemovalNote($member);
        $member->resetPositionAndAssignments();
    }

    /**
     * @param $member
     */
    private function createRemovalNote($member)
    {
        $note = Note::create([
            'type' => 'negative',
            'body' => $this->removal_reason,
            'author_id' => auth()->id(),
            'member_id' => $member->id
        ]);

        $note->tags()->attach($this->input('tag_list'));
    }

}
