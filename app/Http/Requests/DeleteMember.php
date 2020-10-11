<?php

namespace App\Http\Requests;

use App\Models\Note;
use App\Notifications\MemberRemoved;
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
            'removal_reason' => 'required'
        ];
    }

    /**
     * Persist the form
     */
    public function persist()
    {
        /** @var \App\Models\Member $member */
        $member = $this->route('member');

        if ($member->division()->exists()) {
            if ($member->division->settings()->get('slack_alert_removed_member')) {
                $member->division->notify(new MemberRemoved($member));
            }
        }

        $this->createRemovalNote($member);
        $member->resetPositionAndAssignments();
    }

    /**
     * @param $member
     */
    private function createRemovalNote($member)
    {
        Note::create([
            'type' => 'negative',
            'body' => $this->removal_reason,
            'author_id' => auth()->id(),
            'member_id' => $member->id
        ]);
    }
}
