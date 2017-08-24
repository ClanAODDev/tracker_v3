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

    /**
     * Persist the form
     */
    public function persist()
    {
        $member = $this->route('member');
        $this->createRemovalNote($member);
        $member->resetPositionsAndAssignments();
    }

    /**
     * @param $member
     */
    private function createRemovalNote($member)
    {
        if ($this->input('removal-reason') == 'inactivity') {
            $note = Note::create([
                'type' => 'negative',
                'body' => 'Member removed for inactivity',
                'author_id' => auth()->user()->id,
                'member_id' => $member->clan_id
            ]);

            $note->tags()->sync([Tag::whereName('inactivity removal')->first()->id]);
        }
    }
}
