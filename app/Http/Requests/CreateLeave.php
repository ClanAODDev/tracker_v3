<?php

namespace App\Http\Requests;

use App\Leave;
use App\Note;
use App\Tag;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateLeave extends FormRequest
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
        return [
            'end_date' => 'date|after:today',
            'member_id' => [
                'exists:members,clan_id',
                'unique:leaves,member_id'
            ]
        ];
    }

    public function messages()
    {
        return [
            'member_id.exists' => 'Not a valid AOD member',
            'member_id.unique' => 'Member already has a leave of absence'
        ];
    }

    /**
     * store leave and note
     */
    public function persist()
    {
        $note = Note::create([
            'body' => $this->note_body,
            'forum_thread_id' => $this->note_thread_id,
            'type' => 'misc',
            'author_id' => auth()->user(),
            'member_id' => $this->member_id
        ]);

        $leave = Leave::create([
            'reason' => $this->leave_type,
            'end_date' => Carbon::parse($this->end_date),
            'extended' => false
        ]);

        $leave->member()->associate($this->member_id);
        $leave->requester()->associate(auth()->user());
        $leave->note()->associate($note);
        $leave->save();
    }
}
