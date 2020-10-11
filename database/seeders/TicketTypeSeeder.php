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
        \DB::table((new TicketType)->getTable())->insert([
            ['name' => 'Misc', 'description' => "Miscellaneous admin help request"],
            ['name' => 'Forum Change', 'description' => "Change to your forums"],
            ['name' => 'Awards/Medals', 'description' => "Add or update a division award"],
            ['name' => 'Member Rename', 'description' => "Request a member rename"],
            ['name' => 'Teamspeak Change', 'description' => "Change to Teamspeak"],
        ]);
    }
}
