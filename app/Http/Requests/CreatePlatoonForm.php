<?php

namespace App\Http\Requests;

use App\Member;
use App\Platoon;
use Illuminate\Foundation\Http\FormRequest;

class CreatePlatoonForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', [Platoon::class, $this->route('division')]);
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
                'unique:platoons,leader_id'
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
            'leader_id.unique' => 'Already assigned as a leader.',
            'leader_id.exists' => 'Member does not exist.',
        ];
    }

    /**
     * Persist the platoon, and handle member assignment update
     * if a leader was provided
     */
    public function persist()
    {
        $platoon = new Platoon;

        $platoon->name = $this->name;
        $platoon->division()->associate($this->route('division'));
        $platoon->save();

        /**
         * Handle Platoon leader assignment
         */
        if ($this->leader_id) {
            $leader = Member::whereClanId($this->leader_id)->firstOrFail();

            $leader->platoon()
                ->associate($platoon)
                ->assignPosition("platoon leader")
                ->save();

            $platoon->leader()->associate($leader)->save();
        }
    }
}
