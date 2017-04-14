<?php

namespace App\Http\Requests;

use App\Member;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSquadForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('squad'));

        $this->squad = $squad;
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
                // ignore current record
                'unique:squads,leader_id,' . $this->squad->id,
                'exists:members,clan_id',
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

    public function persist()
    {
        $this->squad->update(
            $this->only(['name', 'leader_id'])
        );

        /**
         * Assign leader as leader of squad
         * Place member inside squad
         * Place member inside platoon of squad
         * Assign squad leader position
         */
        if ($this->leader_id) {
            $leader = Member::whereClanId($this->leader_id)->firstOrFail();

            $this->squad->leader()->associate($leader);
            $leader->squad()->associate($this->squad);
            $leader->platoon()->associate($this->route('platoon'));
            $leader->assignPosition("squad leader")->save();
        }
    }
}
