<?php

namespace App\Http\Requests;

use App\Enums\Position;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;

class CreateDivision extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', [Division::class]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:divisions',
            'abbreviation' => 'required|unique:divisions',
            'leader_id' => 'required|exists:members,clan_id',
            'description' => 'required',
        ];
    }

    public function persist()
    {
        $division = Division::create($this->all());
        $division->settings = $division->defaultSettings;
        $division->save();

        if ($this->leader_id) {
            $this->reassignLeaderTo($division);
        }
    }

    /**
     * Reassigns the division leader to the new division.
     */
    private function reassignLeaderTo($division)
    {
        $member = Member::whereClanId($this->leader_id)->first();
        $member->assignPosition(Position::COMMANDING_OFFICER);
        $member->division_id = $division->id;
    }
}
