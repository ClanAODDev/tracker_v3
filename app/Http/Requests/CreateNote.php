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
            'body' => 'required',
            'tag_list' => 'required|array|min:1',
            'forum_thread_id' => 'nullable|numeric'
        ];
    }

    public function messages()
    {
        return [
            'tag_list.required' => 'You must provide at least one tag for your note',
            'body.required' => 'You must provide content for your note',
            'forum_thread_id.numeric' => 'Forum thread ID must be a number'
        ];
    }

    public function persist()
    {
        $note = new Note($this->all());
        $note->member()->associate($this->route('member')->clan_id);
        $note->author()->associate(auth()->user());
        $note->save();

        $note->tags()->attach($this->input('tag_list'));
    }
}
