<?php

namespace Database\Seeders;

use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TicketType::create(['name' => 'Misc', 'description' => "Miscellaneous admin help request"]);
        TicketType::create(['name' => 'Forum Change', 'description' => "Change to your forums"]);
        TicketType::create(['name' => 'Awards & Medals', 'description' => "Add or update a division award"]);
        TicketType::create(['name' => 'Member Rename', 'description' => "Request a member rename"]);
        TicketType::create(['name' => 'Teamspeak Change', 'description' => "Change to Teamspeak"]);
    }
}
