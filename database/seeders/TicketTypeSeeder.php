<?php

namespace Database\Seeders;

use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    private $types = [
        [
            'name' => 'Misc',
            'description' => "Miscellaneous admin help request",
            'display_order' => 900
        ],
        [
            'name' => 'Forum Change',
            'description' => "Change to your forums",
            'display_order' => 100
        ],
        [
            'name' => 'Awards & Medals',
            'description' => "Add or update a division award",
            'display_order' => 100
        ],
        [
            'name' => 'Member Rename',
            'description' => "Request a member rename",
            'display_order' => 100
        ],
        [
            'name' => 'Teamspeak Change',
            'description' => "Change to Teamspeak",
            'display_order' => 100
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->types as $ticketType) {
            TicketType::create($ticketType);
        }
    }
}
