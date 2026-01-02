<?php

namespace App\Http\Requests;

use App\Models\DivisionTag;
use App\Models\Note;
use App\Policies\DivisionTagPolicy;
use Illuminate\Foundation\Http\FormRequest;

class CreateNote extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'body' => 'required',
            'forum_thread_id' => 'nullable|numeric',
            'tag_id' => 'nullable|integer|exists:division_tags,id',
        ];
    }

    public function messages()
    {
        return [
            'body.required' => 'You must provide content for your note',
            'forum_thread_id.numeric' => 'Forum thread ID must be a number',
        ];
    }

    public function persist()
    {
        $member = $this->route('member');
        $user = auth()->user();

        $note = new Note($this->only(['body', 'type', 'forum_thread_id']));
        $note->member_id = $member->id;
        $note->author_id = $user->id;
        $note->save();

        if ($this->filled('tag_id') && $user->can('assign', [DivisionTag::class, $member])) {
            $policy = new DivisionTagPolicy;
            $tag = $policy->getAssignableTags($user, $member)->find($this->input('tag_id'));

            if ($tag) {
                $assignerId = $user->member?->id;
                $member->tags()->syncWithoutDetaching([$tag->id => ['assigned_by' => $assignerId]]);
            }
        }
    }
}
