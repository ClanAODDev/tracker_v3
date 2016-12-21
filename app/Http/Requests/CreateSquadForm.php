<?php

namespace App\Http\Requests;

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
        return $this->user()->can('create', Squad::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'leader' => [
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
            'leader.unique' => 'Member already assigned as a leader.',
            'leader.exists' => 'Member with that clan id does not exist.',
        ];
    }

    public function persist()
    {
        $squad = new Squad;

        $squad->gen_pop = $this->genpop;
        $squad->platoon()->associate($this->route('platoon'));
        $squad->save();

        /**
         * Handle squad leader assignment
         */
        if ($this->leader) {
            $leader = Member::whereClanId($this->leader)->firstOrFail();

            $leader->squad()->associate($squad);
            $leader->platoon()->associate($this->route('platoon'));

            setLeaderOf($squad, $leader);
        }
    }
}
