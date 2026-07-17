<?php

namespace App\Http\Requests\Note;

use App\Models\DivisionTag;
use App\Models\Note;
use App\Policies\DivisionTagPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateNote extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body'            => 'required',
            'type'            => ['required', Rule::in(array_keys(Note::allNoteTypes()))],
            'forum_thread_id' => 'nullable|numeric',
            'tag_id'          => 'nullable|integer|exists:division_tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'body.required'           => 'You must provide content for your note',
            'forum_thread_id.numeric' => 'Forum thread ID must be a number',
        ];
    }

    public function persist(): void
    {
        $member = $this->route('member');
        $user   = auth()->user();

        $note            = new Note($this->only(['body', 'type', 'forum_thread_id']));
        $note->member_id = $member->id;
        $note->author_id = $user->id;
        $note->save();

        if ($this->filled('tag_id') && $user->can('assign', [DivisionTag::class, $member])) {
            $policy = new DivisionTagPolicy;
            $tag    = $policy->getAssignableTags($user, $member)->find($this->input('tag_id'));

            if ($tag) {
                $member->tags()->syncWithoutDetaching([$tag->id => ['assigned_by' => $user->member?->id]]);
            }
        }
    }
}
