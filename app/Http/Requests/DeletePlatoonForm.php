<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeletePlatoonForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('delete', [$this->platoon]);
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
        if ($this->platoon->leader) {
            $this->platoon->leader()->dissociate()->save();
        }

        if ($this->platoon->squads()) {
            $this->cleanUpSquads();
        }

        if ($this->platoon->members()) {
            $this->cleanUpMembers();
        }

        $this->platoon->delete();
    }

    /**
     * Dissociate squad members from any squads.
     */
    private function cleanUpSquads()
    {
        $this->platoon->squads->each(function ($squad) {
            $squad->members->each(function ($member) use ($squad) {
                $member->squad()->dissociate()->save();
                $squad->leader()->dissociate()->save();
                $member->assignPosition('member');
            });

            $squad->platoon()->dissociate();

            $squad->delete();
        });
    }

    /**
     * Dissociate platoon members.
     */
    private function cleanUpMembers()
    {
        $this->platoon->members->each(function ($member) {
            $member->platoon()->dissociate()->save();
        });
    }
}
