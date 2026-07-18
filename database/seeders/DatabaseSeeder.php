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
        if (app()->environment('production')) {
            $this->error('This seeder should not be run in production!');
        }

        Model::unguard();

        $this->call([
            TicketTypeSeeder::class,
        ]);
    }
}
