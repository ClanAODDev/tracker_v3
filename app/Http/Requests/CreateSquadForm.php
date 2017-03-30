<?php

namespace App\Http\Requests;

use App\Division;
use App\Member;
use App\Squad;
use Illuminate\Foundation\Http\FormRequest;

class CreateSquadForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', [Squad::class, $this->route('division')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'leader_id' => [
                'exists:members,clan_id',
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

        $squad->name = $this->name;
        $squad->platoon()->associate($this->route('platoon'));
        $squad->save();

        /**
         * Handle squad leader assignment
         */
        if ($this->leader_id) {
            $leader = Member::whereClanId($this->leader_id)->firstOrFail();

            $squad->leader()->associate($leader)->save();

            $leader->squad()->associate($squad)
                ->platoon()->associate($this->route('platoon'))
                ->assignPosition("squad leader")
                ->save();

        }
    }
}
