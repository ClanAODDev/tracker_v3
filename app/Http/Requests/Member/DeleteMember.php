<?php

namespace App\Http\Requests\Member;

use App\Jobs\RemoveClanMember;
use App\Models\Member;
use App\Models\Note;
use App\Notifications\Channel\NotifyDivisionMemberRemoved;
use App\Notifications\Channel\NotifyDivisionPartTimeMemberRemoved;
use App\Services\AODForumService;
use Illuminate\Foundation\Http\FormRequest;

class DeleteMember extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('separate', [$this->route('member')]);
    }

    public function rules(): array
    {
        return [
            'removal_reason' => 'required',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Member $member */
            $member = $this->route('member');

            if (! Member::isValidForumName($member->name)) {
                $validator->errors()->add(
                    'removal_reason',
                    "Cannot remove {$member->name}: the username contains characters that the forum stores as HTML entities (e.g. &lt; &gt; &amp;), which causes the forum API to reject the removal. Manual intervention required."
                );
            }

            if (AODForumService::hasForumUsernameConflict($member->clan_id, $member->name)) {
                $validator->errors()->add(
                    'removal_reason',
                    "Cannot remove {$member->name}: the username '{$member->name}' is already in use by a different forum account. Manual intervention required."
                );
            }
        });
    }

    public function persist(): void
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
            memberIdBeingRemoved: $member->clan_id,
        );

        $this->notifyPartTimeDivisions($member);
        $this->createRemovalNote($member);
        $member->reset();
    }

    private function createRemovalNote(Member $member): void
    {
        Note::create([
            'type'      => 'misc',
            'body'      => 'Member removal: ' . $this->removal_reason,
            'author_id' => auth()->id(),
            'member_id' => $member->id,
        ]);
    }

    private function notifyPartTimeDivisions(Member $member): void
    {
        $member->partTimeDivisions()->active()->each(
            fn ($division) => $division->notify(new NotifyDivisionPartTimeMemberRemoved($member, $this->removal_reason))
        );
    }
}
