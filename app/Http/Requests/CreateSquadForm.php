<?php

namespace App\Http\Requests;

use App\Squad;
use App\Member;
use App\Division;
use Illuminate\Foundation\Http\FormRequest;

class CreateSquadForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Division $division
     * @return bool
     */
    public function authorize(Division $division)
    {
        return $this->user()->can('create', [Squad::class, $division]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'leader_id' => [
                'sometimes|exists:members,clan_id',
                'unique:squads,leader_id'
            ]
        ];
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'leader_id.unique' => 'Member already assigned as a leader.',
            'leader_id.exists' => 'Member with that clan id does not exist.',
        ];
    }

    public function persist()
    {
        $squad = new Squad;

        $platoon->name = $this->name;
        $squad->platoon()->associate($this->route('platoon'));
        $squad->save();

        /**
         * Handle squad leader assignment
         */
        if ($this->leader) {
            $leader = Member::whereClanId($this->leader_id)->firstOrFail();

            $squad->leader()->associate($leader);
            $leader->squad()->associate($squad);
            $leader->platoon()->associate($this->route('platoon'));
            $leader->assignPosition("squad leader")->save();
        }
    }
}
