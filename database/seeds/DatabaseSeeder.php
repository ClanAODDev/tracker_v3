<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Model::unguard();

        $this->call('DivisionSeeder');
        $this->call('PositionSeeder');
        $this->call('RankSeeder');
        $this->call('RoleSeeder');
        $this->command->info('Tables seeded!');
    }
}
