<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

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
