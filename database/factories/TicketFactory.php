<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'state' => 'new',
            'ticket_type_id' => TicketType::factory(),
            'caller_id' => User::factory(),
            'division_id' => Division::factory(),
            'description' => $this->faker->paragraph,
            'external_message_id' => Uuid::uuid4()->toString(),
            'owner_id' => null,
        ];
    }

    public function assigned(): self
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'assigned',
            'owner_id' => User::factory(),
        ]);
    }

    public function resolved(): self
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'resolved',
            'resolved_at' => now(),
            'owner_id' => User::factory(),
        ]);
    }
}
