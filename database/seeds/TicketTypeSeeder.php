<?php

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
        DB::table('ticket_types')->insert([
            ['name' => 'Misc', 'slug' => 'misc', 'description' => "Miscellaneous admin help request"],
            ['name' => 'Forum Change', 'slug' => 'forum-changes', 'description' => "Change to your forums"],
            ['name' => 'Awards/Medals', 'slug' => 'awards', 'description' => "Add or update a division award"],
            ['name' => 'Member Rename', 'slug' => 'renames', 'description' => "Request a member rename"],
            ['name' => 'Teamspeak Change', 'slug' => 'teamspeak-changes', 'description' => "Change to Teamspeak"],
        ]);
    }
}
