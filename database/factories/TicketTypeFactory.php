<?php

namespace Database\Factories;

use App\Enums\Rank;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name'                 => $name,
            'slug'                 => Str::slug($name),
            'description'          => $this->faker->sentence,
            'boilerplate'          => $this->faker->paragraph,
            'notification_channel' => null,
            'minimum_rank'         => null,
            'role_access'          => null,
            'auto_assign_to_id'    => null,
        ];
    }

    public function restrictedToAdmins(): self
    {
        return $this->state(fn (array $attributes) => [
            'role_access' => ['admin'],
        ]);
    }

    public function restrictedToOfficers(): self
    {
        return $this->state(fn (array $attributes) => [
            'role_access' => ['admin', 'sr_ldr', 'officer'],
        ]);
    }

    public function withNotificationChannel(string $channel): self
    {
        return $this->state(fn (array $attributes) => [
            'notification_channel' => $channel,
        ]);
    }

    public function requiresMinimumRank(Rank $rank): self
    {
        return $this->state(fn (array $attributes) => [
            'minimum_rank' => $rank->value,
        ]);
    }
}
