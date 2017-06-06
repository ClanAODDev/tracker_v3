<?php

namespace App\Http\Requests;

use App\Member;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdatePlatoonForm
 *
 * @package App\Http\Requests
 */
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
            'logo' => 'nullable|url'
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

    /**
     * Save the platoon
     */
    public function persist()
    {
        $this->platoon->update(
            $this->all()
        );

        if ($this->member_ids) {
            $this->assignMembersTo($this->platoon);
        }

        if ($this->leader_id) {
            $this->assignLeaderTo($this->platoon);
        } else {
            $this->resetLeaderFor($this->platoon);
        }
    }

    /**
     * Assign members to a platoon
     *
     * @param $platoon
     */
    private function assignMembersTo($platoon)
    {
        collect(json_decode($this->member_ids))
            ->each(function ($memberId) use ($platoon) {
                $member = Member::find($memberId);
                $member->platoon()->associate($platoon);
                $member->save();
            });
    }

    /**
     * Assign a member as platoon leader
     *
     * @param $platoon
     */
    private function assignLeaderTo($platoon)
    {
        $leader = Member::whereClanId($this->leader_id)->firstOrFail();

        $this->platoon->leader()->associate($leader);
        $leader->platoon()->associate($platoon);
        $leader->squad()->dissociate();

        $leader->assignPosition('platoon leader')->save();
    }

    /**
     * Reset the leader for a platoon
     *
     * @param $platoon
     */
    private function resetLeaderFor($platoon)
    {
        $platoon->leader()->dissociate()->save();
    }
}
