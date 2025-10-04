<?php

namespace App\Http\Requests;

use App\Jobs\RemoveClanMember;
use App\Models\Member;
use App\Models\Note;
use App\Notifications\Channel\NotifydDivisionPartTimeMemberRemoved;
use App\Notifications\Channel\NotifyDivisionMemberRemoved;
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
        return $this->user()->can('separate', [$this->route('member')]);
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
                new NotifyDivisionMemberRemoved($member, auth()->user(), $this->removal_reason, $member->squad)
            );
        }

        RemoveClanMember::dispatch(
            impersonatingMemberId: auth()->user()->member->clan_id,
            memberIdBeingRemoved: $member->clan_id
        );

        $this->notifyPartTimeDivisions($member);
        $this->createRemovalNote($member);
        $member->reset();
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
            $division->notify(new NotifydDivisionPartTimeMemberRemoved($member, $this->removal_reason));
        }
    }
}
