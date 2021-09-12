<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteSquadForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('delete', [$this->squad]);
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
        if ($this->squad->members) {
            $this->squad->members->each(function ($member) {
                $member->squad()->dissociate()->save();
            });
        }

        if ($this->squad->leader) {
            $this->squad->leader()->dissociate()->save();
        }

        $this->squad->delete();
    }
}
