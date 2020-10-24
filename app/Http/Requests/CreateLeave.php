<?php

namespace App\Http\Requests;

use App\Models\Leave;
use App\Models\Member;
use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
class CreateLeave extends \Illuminate\Foundation\Http\FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return ['end_date' => 'date|after:today', 'member_id' => ['exists:members,clan_id', 'unique:leaves,member_id']];
    }
    public function messages()
    {
        return ['member_id.exists' => 'Not a valid AOD member', 'member_id.unique' => 'Member already has a leave of absence'];
    }
    /**
     * store leave and note
     */
    public function persist()
    {
        // search is by clan id, but we want member id (tracker id)
        $memberRequestingLeave = \App\Models\Member::whereClanId($this->member_id)->firstOrFail();
        $note = \App\Models\Note::create(['body' => $this->note_body, 'forum_thread_id' => $this->note_thread_id, 'type' => 'misc', 'author_id' => auth()->user(), 'member_id' => $memberRequestingLeave->id]);
        $leave = \App\Models\Leave::create(['reason' => $this->leave_type, 'end_date' => \Carbon\Carbon::parse($this->end_date), 'extended' => false]);
        $leave->member()->associate($this->member_id);
        $leave->requester()->associate(auth()->user());
        $leave->note()->associate($note);
        $leave->save();
    }
}
