<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Member;
use App\Models\MemberRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberRequestFactory extends Factory
{
    protected $model = MemberRequest::class;

    public function definition(): array
    {
        return [
            'member_id'    => Member::factory(),
            'requester_id' => Member::factory(),
            'division_id'  => Division::factory(),
        ];
    }
}
