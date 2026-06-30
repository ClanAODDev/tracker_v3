<?php

namespace App\Http\Requests\Leave;

use App\Enums\ActivityType;
use App\Models\Leave;
use App\Models\Member;
use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateLeave extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'end_date'  => 'date|after:today',
            'member_id' => ['exists:members,id', 'unique:leaves,member_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.exists' => 'Not a valid AOD member',
            'member_id.unique' => 'Member already has a leave of absence',
        ];
    }

    public function persist(): void
    {
        $memberRequestingLeave = Member::findOrFail($this->member_id);

        $note = Note::create([
            'body'            => "Leave of absence requested. Reason: {$this->note_body}",
            'forum_thread_id' => $this->note_thread_id,
            'type'            => 'misc',
            'author_id'       => auth()->id(),
            'member_id'       => $memberRequestingLeave->id,
        ]);

        $leave = Leave::create([
            'reason'   => $this->leave_type,
            'end_date' => Carbon::parse($this->end_date),
            'extended' => false,
        ]);

        $leave->member()->associate($this->member_id);
        $leave->requester()->associate(auth()->user());
        $leave->note()->associate($note);
        $leave->save();

        $memberRequestingLeave->recordActivity(ActivityType::REQUESTED_LEAVE);
    }
}
