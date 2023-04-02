<?php

namespace App\Http\Requests;

use App\Enums\Position;
use App\Models\Member;
use App\Models\Platoon;
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
            'name' => 'max:40',
            'logo' => 'nullable|url',
            'order' => 'required|integer',
            'leader_id' => [
                'exists:members,clan_id',
                'unique:platoons,leader_id',
            ],
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
            'leader_id.unique' => 'Already assigned as a leader.',
            'leader_id.exists' => 'Member does not exist.',
        ];
    }

    /**
     * Persist the platoon, and handle member assignment update
     * if a leader was provided.
     */
    public function persist()
    {
        $platoon = new Platoon();

        $platoon->name = $this->name;
        $platoon->order = $this->order;
        $platoon->division()->associate($this->route('division'));
        $platoon->save();

        if ($this->member_ids) {
            $this->assignMembersTo($platoon);
        }

        if ($this->leader_id) {
            $this->assignLeaderTo($platoon);
        }
    }

    /**
     * @param $platoon
     */
    private function assignMembersTo($platoon)
    {
        collect(json_decode($this->member_ids))->each(function ($memberId) use ($platoon) {
            $member = Member::find($memberId);
            $member->platoon()->associate($platoon);
            $member->save();
        });
    }

    /**
     * @param $platoon
     */
    private function assignLeaderTo($platoon)
    {
        $leader = Member::whereClanId($this->leader_id)->firstOrFail();

        $leader->platoon()
            ->associate($platoon)
            ->assignPosition(Position::PLATOON_LEADER)
            ->save();

        $platoon->leader()->associate($leader)->save();
    }
}
