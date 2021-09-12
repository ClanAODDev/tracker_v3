<?php

namespace App\Http\Requests;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdatePlatoonForm.
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
            'name' => 'max:40',
            'logo' => 'nullable|url',
        ];
    }

    /**
     * Custom error messages.
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
     * Save the platoon.
     */
    public function persist()
    {
        if ($this->member_ids) {
            $this->assignMembersTo($this->platoon);
        }

        // setting platoon to TBA
        if (!$this->leader && !$this->leader_id) {
            // reset existing leader if there is one
            if ($this->platoon->leader) {
                $this->resetLeaderOf($this->platoon);
            }
        }

        // setting a new leader
        if ($this->leader_id) {
            // is there an existing leader?
            if ($this->platoon->leader) {
                $this->resetLeaderOf($this->platoon);
            }

            // set the new leader
            $this->assignLeaderTo($this->platoon);
        }

        // handle all the other updates
        $this->platoon->update(
            $this->all()
        );
    }

    /**
     * Assign members to a platoon.
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
     * Reset the leader for a platoon.
     *
     * @param $platoon
     */
    private function resetLeaderOf($platoon)
    {
        $platoon->leader->assignPosition('member')->save();

        $platoon->leader()->dissociate()->save();
    }

    /**
     * Assign a member as platoon leader.
     *
     * @param $platoon
     */
    private function assignLeaderTo($platoon)
    {
        $leader = Member::whereClanId($this->leader_id)->firstOrFail();

        if ($leader->squad) {
            $squad = $leader->squad;
            $squad->leader()->dissociate()->save();
            $leader->squad()->dissociate()->save();
        }

        $this->platoon->leader()->associate($leader);

        $leader->platoon()->associate($platoon);

        $leader->assignPosition('platoon leader')->save();
    }
}
