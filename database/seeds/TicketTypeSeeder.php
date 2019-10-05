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
            ['name' => 'Misc', 'slug' => 'misc', 'description' => "Use this if your ticket does not fit an existing type"],
            ['name' => 'Moderator Change', 'slug' => 'mod-change', 'description' => "Request a change of your division's moderators."],
            ['name' => 'Forum Change', 'slug' => 'forum-change', 'description' => "Request a change to your forums"],
            ['name' => 'Award Request', 'slug' => 'awards', 'description' => "Request to add or update a division award"],
            ['name' => 'Rename Member', 'slug' => 'renames', 'description' => "Request a member rename"],
        ]);
    }
}
