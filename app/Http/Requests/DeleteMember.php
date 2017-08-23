<?php

namespace App\Http\Requests;

use App\Note;
use App\Tag;
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
            'removal-reason' => 'required'
        ];
    }

    public function persist()
    {
        $this->createRemovalNote();
        $member = $this->route('member');
        $member->resetPositionsAndAssignments();
        $member->division_id = 0;

        $member->save();

    }

    private function createRemovalNote()
    {
        if ($this->input('removal-reason') == 'inactivity') {
            $note = Note::create();
            $note->type = 'negative';
            $note->body = "Member removed for inactivity";
            $note->author()->associate(auth()->user());
            $note->tags()->sync([Tag::whereName('inactivity removal')->first()->id]);
            $note->member()->associate($this->route('member'));
            $note->save();
        }
    }
}
