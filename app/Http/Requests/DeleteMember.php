<?php

namespace App\Http\Requests;

use App\Models\Member;
use App\Models\Note;
use App\Notifications\MemberRemoved;
use App\Notifications\PartTimeMemberRemoved;
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
            'removal_reason' => 'required',
        ];
    }

    /**
     * Persist the form.
     */
    public function persist()
    {
        /** @var Member $member */
        $member = $this->route('member');

        if ($member->division()->exists()) {
            $member->division->notify(
                new MemberRemoved($member, auth()->user(), $this->removal_reason, $member->squad)
            );
        }

        $this->notifyPartTimeDivisions($member);
        $this->createRemovalNote($member);
        $member->resetPositionAndAssignments();
    }

    private function createRemovalNote($member)
    {
        Note::create([
            'type' => 'negative',
            'body' => 'Member removal:' . $this->removal_reason,
            'author_id' => auth()->id(),
            'member_id' => $member->id,
        ]);
    }

    private function notifyPartTimeDivisions(Member $member)
    {
        $divisions = $member->partTimeDivisions()->active()->get();

        foreach ($divisions as $division) {
            $division->notify(new PartTimeMemberRemoved($member, $this->removal_reason));
        }
    }
}
