<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Model::unguard();

        $this->call([
            PositionSeeder::class,
            RankSeeder::class,
            RoleSeeder::class,
            TicketTypeSeeder::class,
        ]);
    }
}
