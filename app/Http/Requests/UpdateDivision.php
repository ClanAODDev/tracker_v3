<?php

namespace App\Http\Requests;

use App\Tag;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDivision extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('division'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

        ];
    }

    public function persist()
    {
        $this->route('division')->settings()->merge($this->all());

       /* if ($this->division_tags) {
            $this->cleanTags();
            $this->createNewTags();
        }*/
    }

    /**
     * Since a one-to-many sync does not exist, we must first
     * wipe existing tags before assigning new ones.
     */
    private function cleanTags()
    {
        $this->division->tags->each(function ($tag) {
            $tag->delete();
        });
    }

    /**
     * Create our new tags
     */
    private function createNewTags()
    {
        collect(array_flatten($this->division_tags))->each(function ($tagName) {
            $tag = new Tag;
            $tag->name = $tagName;
            $tag->division()->associate($this->division);
            $tag->slug = str_slug($tagName);
            $tag->save();
        });
    }
}
