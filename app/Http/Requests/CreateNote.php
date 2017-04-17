<?php

namespace App\Http\Requests;

use App\Note;
use Illuminate\Foundation\Http\FormRequest;

class CreateNote extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // anyone can create a note
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
            'note-body' => 'required',
            'forum-thread' => 'nullable|numeric'
        ];
    }

    public function messages()
    {
        return [
            'note-body.required' => 'You must provide content for your note',
            'forum-thread.numeric' => 'Forum thread ID must be a number'
        ];
    }

    public function persist()
    {
        $note = new Note([
            'body' => $this['note-body'],
            'type' => $this['note-type'],
            'forum_thread_id' => $this['forum-thread'],
            'member_id' => $this->route('member')->clan_id,
            'author_id' => auth()->user()->id
        ]);

        $note->save();
    }
}
