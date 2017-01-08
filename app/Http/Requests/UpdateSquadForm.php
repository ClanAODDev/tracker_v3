<?php

namespace App\Http\Requests;

use App\Division;
use App\Member;
use App\Squad;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSquadForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Squad $squad
     * @param Division $division
     * @return bool
     */
    public function authorize(Squad $squad, Division $division)
    {
        return $this->user()->can('update', [$squad, $division]);
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
                'exists:members,clan_id',
                'unique:squads,leader_id',
            ],
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

    public function persist(Squad $squad)
    {
        $squad->update([
            'gen_pop' => $this->gen_pop,
            'leader_id' => $this->leader_id,
        ]);

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
