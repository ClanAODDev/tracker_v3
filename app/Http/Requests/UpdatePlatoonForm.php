<?php

namespace App\Http\Requests;

use App\Member;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatoonForm extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', [$this->platoon]);
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
                "unique:platoons,leader_id,{$this->platoon->id}",
                'exists:members,clan_id',
            ],
            'logo' => 'url'
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
        $this->platoon->update(
            $this->all()
        );

        if ($this->member_ids) {
            collect(json_decode($this->member_ids))->each(function ($memberId) {
                $member = Member::find($memberId);
                $member->platoon()->associate($this->platoon);
                $member->save();
            });
        }

        /**
         * Assign leader as leader of platoon
         * Place member inside platoon
         * Assign platoon leader position
         */
        if ($this->leader_id) {
            $leader = Member::whereClanId($this->leader_id)->firstOrFail();

            $this->platoon->leader()->associate($leader);
            $leader->platoon()->associate($this->platoon);
            $leader->squad()->dissociate();
            $leader->assignPosition("platoon leader")->save();
        }
    }
}
