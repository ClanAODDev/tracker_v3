<?php

namespace Database\Factories;

use App\Models\Leave;
use App\Models\Member;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        $member = Member::factory()->create();

        return [
            'member_id' => $member->clan_id,
            'requester_id' => User::factory(),
            'approver_id' => User::factory(),
            'reason' => $this->faker->randomElement(array_keys(Leave::$reasons)),
            'note_id' => Note::factory()->create(['member_id' => $member->id])->id,
            'end_date' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
        ];
    }

    public function expired(): self
    {
        return $this->state(fn (array $attributes) => [
            'end_date' => $this->faker->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }

    public function extended(): self
    {
        return $this;
    }

    public function military(): self
    {
        return $this->state(fn (array $attributes) => [
            'reason' => 'military',
        ]);
    }

    public function medical(): self
    {
        return $this->state(fn (array $attributes) => [
            'reason' => 'medical',
        ]);
    }
}
