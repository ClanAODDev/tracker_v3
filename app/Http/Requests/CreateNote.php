<?php

namespace App\Http\Requests;

use App\Models\Note;
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
            'body'            => 'required',
            'forum_thread_id' => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'body.required'           => 'You must provide content for your note',
            'forum_thread_id.numeric' => 'Forum thread ID must be a number',
        ];
    }

    public function persist()
    {
        $note = new Note($this->all());
        $note->member_id = $this->route('member')->id;
        $note->author_id = auth()->id();
        $note->save();
    }
}
